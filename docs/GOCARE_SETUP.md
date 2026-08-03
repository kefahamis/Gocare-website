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
