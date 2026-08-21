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
