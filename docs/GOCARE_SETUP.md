# GoCare Laravel Setup

## Local Application

The Laravel application is in `D:/Sites/Gocare-laravel`.

1. Configure `.env` with the MySQL database and SMTP provider values.
2. Create the `gocare` MySQL database.
3. Run `php artisan migrate --seed`.
4. Run `npm install` and `npm run build`.
5. Start the site with `php artisan serve`.

The public site is available at `/`. The Filament dashboard is available at `/admin`.

## Admin Account

The seed reads `ADMIN_EMAIL`, `ADMIN_NAME`, and `ADMIN_PASSWORD` from `.env` and creates a verified administrator account.

Change the password before deploying.

## Email

Set these `.env` values for production SMTP delivery:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.provider.example
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
GOCARE_NOTIFICATION_EMAIL=admissions@example.com
```

Contact submissions and applications are stored in MySQL before notification emails are sent.

## M-Pesa Payment Verification (manual payments)

Step 5 of the application form offers **Pay manually instead**: the applicant pays the
application fee themselves, then enters the confirmation code from the M-Pesa SMS. The
site confirms that code against the Safaricom Daraja
[Transaction Status API](https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query).

This is separate from the STK push and does not change it. It shares the Daraja
credentials but keeps its own config file (`config/application_payments.php`), its own
routes (registered by `ApplicationPaymentServiceProvider`, not `routes/web.php`) and
its own `MPESA_STATUS_*` env keys, so nothing here can clash with the STK push.

### Configuration

These are already set for the STK push and are read as-is:

```env
MPESA_ENV=live
MPESA_CONSUMER_KEY=...
MPESA_CONSUMER_SECRET=...
MPESA_TYPE=till
MPESA_SHORTCODE=7425170
MPESA_TILL_NUMBER=9373479
```

These are new and must be added:

```env
GOCARE_APPLICATION_FEE=1000
MPESA_IDENTIFIER_TYPE=4
MPESA_STATUS_INITIATOR=your-api-operator-username
MPESA_STATUS_INITIATOR_PASSWORD=your-api-operator-password
MPESA_STATUS_CALLBACK_SECRET=a-long-random-string
MPESA_STATUS_RESULT_URL=https://your-domain/mpesa/verification/callback/result/a-long-random-string
MPESA_STATUS_TIMEOUT_URL=https://your-domain/mpesa/verification/callback/timeout/a-long-random-string
```

`MPESA_SHORTCODE` is the head office / store number and becomes the `PartyA` of the
query; `MPESA_TILL_NUMBER` is the till customers pay to and is what the form shows
them. `MPESA_IDENTIFIER_TYPE` is `1` for an MSISDN, `2` for a till number and `4` for
an organization shortcode — use `4` with the store number. If Safaricom rejects the
query with an invalid-party error, the two numbers are the wrong way round.

`MPESA_TYPE` also drives the on-screen instructions: `till` shows Buy Goods and
Services with the till number, `paybill` shows Pay Bill with a business number and
`GOCARE_MPESA_ACCOUNT_HINT` as the account.

The initiator is an API operator created in the M-Pesa org portal with the
**Transaction Status Query** role. It is not the STK passkey, which this feature never
reads. Download the Safaricom production certificate to
`storage/app/mpesa/production.cer` so the initiator password can be encrypted into a
security credential, or put an already-encrypted one in
`MPESA_STATUS_SECURITY_CREDENTIAL`.

Run `php artisan migrate` to create the `mpesa_verifications` table.

### How a verification runs

1. The browser posts the code to `POST /mpesa/verification/query`.
2. The site queries Daraja, which only acknowledges the request.
3. Safaricom posts the real outcome to `MPESA_STATUS_RESULT_URL` (or the timeout URL).
4. The browser polls `GET /mpesa/verification/status/{token}` until the outcome
   arrives, then fills in the transaction code and payment date.

Both callback URLs must be publicly reachable over HTTPS. Safaricom does not sign its
callbacks, so `MPESA_STATUS_CALLBACK_SECRET` must be part of the URL — without a
matching secret the callbacks return 404. Leave the secret blank only for local
testing.

A code is accepted only when Safaricom reports `ResultCode 0`, a completed transaction,
payment to the till or store number, and at least `GOCARE_APPLICATION_FEE`. Every
attempt is recorded in `mpesa_verifications`, and a confirmed code cannot be claimed by
a second applicant.
