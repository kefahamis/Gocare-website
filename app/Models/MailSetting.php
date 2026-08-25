<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class MailSetting extends Model
{
    protected $fillable = [
        'enabled', 'host', 'port', 'encryption', 'username', 'password',
        'from_address', 'from_name', 'last_tested_to', 'last_tested_at', 'last_test_error',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'port' => 'integer',
        // Encrypted at rest: the plaintext never reaches the database, a query
        // log, or a backup.
        'password' => 'encrypted',
        'last_tested_at' => 'datetime',
    ];

    /**
     * The single settings row, created empty on first use.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], ['enabled' => false, 'port' => 587]);
    }

    /**
     * Push these settings into the live mail config.
     *
     * Called on every request from AppServiceProvider, which is why it has to
     * survive a database that is not ready: during `migrate` on a fresh clone
     * the table does not exist yet, and a hard failure there would take the
     * whole site down rather than just leaving mail on its .env defaults.
     */
    public static function applyToConfig(): void
    {
        try {
            if (! Schema::hasTable('mail_settings')) {
                return;
            }

            $settings = static::query()->first();
        } catch (\Throwable) {
            return;
        }

        if (! $settings || ! $settings->enabled) {
            return;
        }

        // Anything blank falls back to the .env value rather than overwriting
        // it with nothing -- a half-filled form must not break a working setup.
        $host = $settings->host ?: config('mail.mailers.smtp.host');
        $username = $settings->username ?: config('mail.mailers.smtp.username');
        $password = $settings->password ?: config('mail.mailers.smtp.password');

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => $settings->port ?: config('mail.mailers.smtp.port'),
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.password' => $password,
            // Laravel 11+ reads `scheme`; `encryption` is kept for older
            // transports that still look for it.
            'mail.mailers.smtp.scheme' => 'ssl' === $settings->encryption ? 'smtps' : null,
            'mail.mailers.smtp.encryption' => $settings->encryption ?: null,
        ]);

        if ($settings->from_address) {
            config(['mail.from.address' => $settings->from_address]);
        }

        if ($settings->from_name) {
            config(['mail.from.name' => $settings->from_name]);
        }
    }
}
