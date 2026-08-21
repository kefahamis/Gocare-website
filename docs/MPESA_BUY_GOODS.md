# M-Pesa Buy Goods (Till) Support

This document describes what is needed to add **Buy Goods (Till number)** payments to the application fee flow, alongside the existing **Pay Bill** integration.

The current integration (see `app/Services/MpesaService.php`) only initiates Pay Bill payments via STK Push:

- `TransactionType = 'CustomerPayBillOnline'`
- Funds land in the **Pay Bill** shortcode
- `BusinessShortCode` / `PartyB` = Pay Bill number
- Password is built from the Pay Bill shortcode + Pay Bill passkey

**Buy Goods** is the other C2B flow supported by the Daraja STK Push API. The customer's money lands in a **Till number** instead of a Pay Bill. It is a different transaction type, uses a different destination shortcode (the Till), and — in production — a Till-specific passkey.

## 1. Overview: Pay Bill vs Buy Goods

| | Pay Bill (current) | Buy Goods (Till) |
| --- | --- | --- |
| `TransactionType` | `CustomerPayBillOnline` | `CustomerBuyGoodsOnline` |
| Destination | Pay Bill shortcode | Till number (5–6 digits) |
| `BusinessShortCode` | Pay Bill number | Till number |
| `PartyB` | Pay Bill number | Till number |
| `AccountReference` | Required (shown to payer) | Optional (any value allowed) |
| Passkey | Pay Bill passkey | Till passkey (production) |
| Typical use | Fees, bills, utilities | Retail, goods/services |

Notes:

- The wire name for the STK Push type is `CustomerBuyGoodsOnline`. In the C2B validation callback Safaricom reports the transaction type as the human-readable string `"Buy Goods"` (not the CommandID string).
- STK Push `Amount` must be a whole number of KES — no cents. Round before sending.
- The same STK Push endpoint is used: `POST /mpesa/stkpush/v1/processrequest`.

## 2. Prerequisites (Safaricom side)

Before any Buy Goods transaction can work, the following must exist:

1. A registered **Lipa Na M-Pesa Till number** (Buy Goods).
2. **Go-Live approval** on the Daraja portal for the app, with the Till attached. The Go-Live form typically asks for:
   - BRS Certificate of Registration (PDF)
   - KRA PIN certificate
   - The phone number registered as the Till owner / supervisor
   - The Till number (Buy Goods)
   - The business email registered with Safaricom for the Till
   - A short description of how M-Pesa will be used
3. The **Till passkey**. Production passkeys are delivered by email after Go-Live approval and are **specific to each Pay Bill / Till**. The sandbox passkey (`bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919`) is a fixed test value and is NOT valid for production Tills.
4. A **public, HTTPS callback URL**. Daraja rejects HTTP and self-signed certificates in production. Confirmed: the Till must actually be enabled for online collection ("Lipa Na M-Pesa Online"); contact Safaricom Business (0722 002 100) if it is registered but not activated.

Go-Live approval typically takes 1–3 weeks.

## 3. Environment / config changes

Add the following to `.env` and `.env.example`:

```env
# Existing (Pay Bill)
MPESA_ENV=sandbox
MPESA_CONSUMER_KEY=YOUR_CONSUMER_KEY
MPESA_CONSUMER_SECRET=YOUR_CONSUMER_SECRET
MPESA_PASSKEY=bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919
MPESA_SHORTCODE=174379

# New (Buy Goods / Till)
MPESA_TILL=YOUR_TILL_NUMBER
MPESA_TILL_PASSKEY=YOUR_TILL_PASSKEY
# Which method the form offers by default: paybill | buygoods
MPESA_DEFAULT_PAYMENT=paybill
```

Corresponding additions in `config/mpesa.php`:

```php
'till' => env('MPESA_TILL'),
'till_passkey' => env('MPESA_TILL_PASSKEY'),
'default_payment' => env('MPESA_DEFAULT_PAYMENT', 'paybill'),
```

Sandbox values for testing:

- Sandbox shortcode: `174379` (also accepted as a Till in sandbox)
- Sandbox passkey: `bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919`
- Test SIM: `254708374149` (accepts any PIN and returns success)
- Sandbox simulator C2B Till (for C2B register/simulation): `600984`

## 4. Backend changes required

### 4.1 `app/Services/MpesaService.php`

`stkPush()` currently hardcodes the Pay Bill transaction type, shortcode and passkey. To support both methods it needs a payment-type parameter, e.g. `$paymentMethod = 'paybill'` (default), and branches as follows:

```php
$isBuyGoods = $paymentMethod === 'buygoods';

$shortcode = $isBuyGoods ? config('mpesa.till') : $this->shortcode;
$passkey   = $isBuyGoods ? config('mpesa.till_passkey') : $this->passkey;
$password  = base64_encode($shortcode . $passkey . $timestamp);

$body = [
    'BusinessShortCode'   => $shortcode,
    'Password'            => $password,
    'Timestamp'           => $timestamp,
    'TransactionType'     => $isBuyGoods ? 'CustomerBuyGoodsOnline' : 'CustomerPayBillOnline',
    'Amount'              => (int) round($amount),
    'PartyA'              => $phoneNumber,
    'PartyB'              => $shortcode,
    'PhoneNumber'         => $phoneNumber,
    'CallBackURL'         => config('mpesa.callback_url'),
    'AccountReference'    => $isBuyGoods ? substr($reference, 0, 12) : substr($reference, 0, 12),
    'TransactionDesc'     => substr($description, 0, 13),
];
```

Key points:

- For Buy Goods, **both** `BusinessShortCode` and `PartyB` must be the Till number.
- The `Password` is always `base64(shortcode + passkey + timestamp)` — the correct shortcode/passkey pair for the chosen method must be used.
- `AccountReference` is optional for Till transactions but keeping the application reference is still useful for reconciliation.

### 4.2 `app/Http/Controllers/ApplicationController.php`

- Accept a `payment_method` field from the form (`paybill` / `buygoods`) and pass it through to `stkPush()`.
- Keep validation: `phone` required, `amount` required/numeric, `payment_method` in `paybill,buygoods`.

### 4.3 `app/Models/Application.php`

`$fillable` currently only contains `['reference', 'status', 'data', 'submitted_at']` — it is missing the M-Pesa columns. Add:

```php
protected $fillable = [
    'reference', 'status', 'data', 'submitted_at',
    'phone', 'amount', 'checkout_request_id', 'mpesa_receipt', 'payment_status',
];
```

## 5. Frontend changes required

The apply forms (`resources/views/pages/apply.blade.php` and `resources/views/pages/application-form-step6.blade.php`) need a payment-method selector:

- M-Pesa **Pay Bill**
- M-Pesa **Buy Goods (Till)** — instruct the applicant to pay to the Till number

The selected method should be sent to `/applications` as `payment_method`. The phone number input and STK Push flow stay the same for both options. The application fee (`amount`) must stay a whole number of KES.

## 6. Callback handling

No changes are strictly required in `ApplicationController::mpesaCallback()` — the same `/mpesa/callback` endpoint receives callbacks for both Pay Bill and Buy Goods, keyed by `CheckoutRequestID`. Success is still `ResultCode === 0` and the receipt number is read from `CallbackMetadata`.

Useful detail: the C2B validation payload's `TransactionType` field reports `"Buy Goods"` or `"Pay Bill"` (human-readable), which can be logged for reconciliation.

## 7. Testing

1. Set `MPESA_ENV=sandbox` and use the sandbox values from section 3.
2. Push a test transaction with the test SIM `254708374149` and confirm the callback marks `payment_status = paid`.
3. Verify the `Password`/`Timestamp` rule: timestamp format `YYYYMMDDHHmmss` in East Africa Time, identical in both the password and the top-level `Timestamp` field, and within 5 minutes of the server clock (NTP the server).
4. Before going live, run a single real KSh 1 transaction against your own phone and confirm the money lands in the Till.

## 8. Known issues to fix first

Neither payment method will work end-to-end until these are resolved:

1. **`app/Http/Controllers/ApplicationController.php` is corrupted.** Every `$` variable prefix has been replaced with `\` (e.g. `\->validate(...)` instead of `$request->validate(...)`). The file will not parse. It must be rewritten.
2. **`app/Models/Application.php` `$fillable` is missing the M-Pesa columns** (see section 4.3).
3. **Callback URL mismatch.** The route is `POST /mpesa/callback` (`routes/web.php:18`), but the config default is `APP_URL . '/api/mpesa/callback'` and `.env` sets `MPESA_CALLBACK_URL="/api/mpesa/callback"`. The configured URL must match the actual route, otherwise Safaricom's callback is never delivered. This must be corrected before testing.
4. **`.env` still contains placeholder credentials** (`MPESA_CONSUMER_KEY=YOUR_CONSUMER_KEY`, etc.). Replace with real Daraja credentials.

## 9. Confirming a manually typed code (Transaction Status)

When the STK prompt never arrives, the applicant pays from their own M-Pesa menu and
types the confirmation code from the SMS into **Pay manually via M-Pesa**. Previously
that code was recorded and trusted; it is now checked against the Daraja
[Transaction Status API](https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query)
before the application can reach `payment_status = paid`.

### Environment

The query reuses `MPESA_ENV`, `MPESA_CONSUMER_KEY`, `MPESA_CONSUMER_SECRET`,
`MPESA_SHORTCODE` and `MPESA_TILL_NUMBER`. Only these are new:

```env
MPESA_STATUS_INITIATOR=your-api-operator-username
MPESA_STATUS_INITIATOR_PASSWORD=your-api-operator-password
MPESA_STATUS_IDENTIFIER_TYPE=4
MPESA_STATUS_RESULT_URL=https://your-domain/mpesa/status/result
MPESA_STATUS_TIMEOUT_URL=https://your-domain/mpesa/status/timeout
```

The initiator is an API operator created in the M-Pesa org portal with the
**Transaction Status Query** role. It is *not* `MPESA_PASSKEY`, which stays STK-only.
Either set `MPESA_STATUS_SECURITY_CREDENTIAL` to an already-encrypted credential, or
leave it blank and place the Safaricom production certificate at
`storage/app/mpesa/production.cer` so the initiator password is encrypted at runtime
(override the path with `MPESA_STATUS_CERTIFICATE_PATH`).

`PartyA` is `MPESA_SHORTCODE`, the Head Office / store number the passkey was issued
against — the same number the STK password is built from, not the till customers pay
to. `MPESA_STATUS_IDENTIFIER_TYPE` is `1` for an MSISDN, `2` for a till number and `4`
for an organization shortcode; use `4` with the store number. If Safaricom answers with
an invalid-party error, the store and till numbers are the wrong way round.

Both URLs must be absolute `https://` and publicly reachable, exactly like
`MPESA_CALLBACK_URL`. The service refuses to send the query otherwise rather than
letting Daraja drop it silently. Their routes are exempt from CSRF alongside
`mpesa/callback`.

Run `php artisan migrate` for the new `status_conversation_id` and `payment_note`
columns on `applications`.

### Flow

1. `POST /applications/paid` parks the claim as `awaiting_verification`, stores the
   code in `mpesa_receipt`, and sends the Transaction Status query.
2. Daraja only acknowledges; the real outcome arrives later at
   `POST /mpesa/status/result`, matched back by the `OriginatorConversationID` saved in
   `status_conversation_id`.
3. The browser polls `/applications/status/{reference}` until `payment_status` becomes
   `paid` or a `payment_note` explains why the code was not accepted.

A code is accepted only when Safaricom reports `ResultCode 0`, a completed transaction,
payment credited to the till or store number, and an amount at least the application
fee. A code already confirmed against another application is refused outright.

### Deliberate fallbacks

- **Leave `MPESA_STATUS_INITIATOR` blank** and the manual flow behaves exactly as it
  did before: the code is recorded as `awaiting_verification` for an admin to reconcile
  and nothing is sent to Safaricom. This is the safe way to deploy the change first and
  switch verification on afterwards.
- **A failed check never sets `payment_status = failed`.** A mistyped code would
  otherwise cost an applicant a payment they really made, so the claim stays
  `awaiting_verification` with the reason in `payment_note`.
- **A Daraja outage still records the claim.** Verification is a check on top of the
  existing behaviour, never a gate in front of it.
- **An STK-confirmed payment is never walked back** by a later status result.

### Residual risk

Like `/mpesa/callback`, the result endpoint is unauthenticated — Safaricom does not
sign its callbacks. A result is only accepted when its `OriginatorConversationID`
matches one this site generated, which is the same bar the STK callback sets with
`CheckoutRequestID`. If you want a stronger guarantee, put an unguessable segment in
`MPESA_STATUS_RESULT_URL` and match the route accordingly.

## 10. Idempotency

Payments are retried by everyone: applicants tap "try again", and Safaricom re-delivers
a callback it believes was not acknowledged. Neither may double-charge, double-record
or double-notify — while the STK prompt itself stays deliberately re-sendable.

| Repeated action | Guarantee |
| --- | --- |
| `POST /applications` again | Reuses the session's application. One applicant, one row. |
| `POST /applications` again | Still sends a fresh STK prompt — retrying is the point. |
| `POST /applications` when already `paid` | Returns the paid state. No second prompt, no second row. |
| `POST /mpesa/callback` re-delivered | Transitions to `paid` once; one notification email. |
| Late *failed* callback after `paid` | Ignored; never unpays. |
| `POST /applications/paid` with the same code | No second Transaction Status query while one is in flight. |
| `POST /applications/paid` with a corrected code | Queried — fixing a typo must still work. |
| `POST /mpesa/status/result` re-delivered | Transitions to `paid` once; one notification email. |
| Status result and STK callback both arrive | Whichever lands first wins; one email total. |

How it is enforced:

- **One row per applicant.** `store()` reuses the application the session owns instead
  of creating another. A second row would be orphaned: a late callback for the first
  attempt would settle it while the browser polls the newer reference, so the applicant
  pays and never sees confirmation.
- **Every attempt's CheckoutRequestID is kept** in `checkout_request_ids`, and the
  callback matches the latest *or* any earlier one. An applicant who pays the first
  prompt after asking for a second is still credited.
- **The transition to `paid` is claimed atomically** in both callbacks with a
  conditional `where('payment_status', '!=', 'paid')->update(...)`. Only the writer that
  actually changed a row sends the email, so a re-delivery or a race between the STK
  callback and a status result cannot notify twice.
- **A query in flight is not repeated.** `status_queried_at` plus the stored
  conversation id suppress a duplicate query for the same code for two minutes, matching
  how long the browser polls. After that a retry is allowed, so a result Safaricom never
  delivered is recoverable.
- **A retry never downgrades a stronger state.** `store()` resets only `pending` or
  `failed` rows to `pending`; a code already `awaiting_verification` is left alone, and a
  `paid` row is never touched.
