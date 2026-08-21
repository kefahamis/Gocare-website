<?php

namespace App\Console\Commands;

use App\Services\MpesaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class MpesaDiagnose extends Command
{
    protected $signature = 'mpesa:diagnose
        {--phone= : Send a real STK push to this number}
        {--shortcode= : Override BusinessShortCode (and the password base)}
        {--party-b= : Override PartyB (the till customers pay to)}
        {--type= : Override paybill|till}';

    protected $description = 'Check every stage of the M-Pesa STK push path';

    public function handle(): int
    {
        // Runtime overrides let you hunt for the right shortcode/PartyB
        // pairing without editing .env between attempts.
        foreach (['shortcode' => 'mpesa.shortcode', 'party-b' => 'mpesa.till_number', 'type' => 'mpesa.type'] as $opt => $key) {
            if ($this->option($opt)) {
                config([$key => $this->option($opt)]);
                $this->warn('override: '.$key.' = '.$this->option($opt));
            }
        }

        $env  = strtolower((string) config('mpesa.env'));
        $live = in_array($env, ['production', 'live'], true);
        $base = $live ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke';

        $this->line('');
        $this->info('1. Resolved config');
        $this->table(['key', 'value'], [
            ['env',          $env.($live ? '  -> '.$base : '  -> '.$base)],
            ['type',         (string) config('mpesa.type')],
            ['shortcode',    (string) config('mpesa.shortcode')],
            ['till_number',  (string) (config('mpesa.till_number') ?: '(unset, falls back to shortcode)')],
            ['consumer_key', $this->mask((string) config('mpesa.consumer_key'))],
            ['consumer_sec', $this->mask((string) config('mpesa.consumer_secret'))],
            ['passkey',      $this->mask((string) config('mpesa.passkey'))],
            ['APP_URL',      (string) config('app.url')],
            ['callback_url', (string) config('mpesa.callback_url')],
        ]);

        // 2. Callback URL guard -- this is what MpesaService checks first.
        $this->info('2. Callback URL');
        $callback = (string) config('mpesa.callback_url');
        if (! str_starts_with($callback, 'https://')) {
            $this->error('   FAIL  not an absolute https:// URL: '.($callback ?: '(empty)'));
            $this->warn('   stkPush() throws here before ever contacting Safaricom.');
            $this->warn('   Fix: set MPESA_CALLBACK_URL and APP_URL in .env, then php artisan config:clear');

            return self::FAILURE;
        }
        $this->line('   OK    '.$callback);

        // 3. Outbound connectivity -- shared hosts often firewall this off.
        $this->info('3. Outbound connection to '.$base);
        $host = parse_url($base, PHP_URL_HOST);
        $ip   = gethostbyname($host);
        if ($ip === $host) {
            $this->error('   FAIL  DNS did not resolve '.$host);

            return self::FAILURE;
        }
        $this->line('   DNS   '.$host.' -> '.$ip);

        $sock = @fsockopen('ssl://'.$host, 443, $errno, $errstr, 10);
        if (! $sock) {
            $this->error('   FAIL  cannot open :443 -- '.$errstr.' ('.$errno.')');
            $this->warn('   Your host is blocking outbound HTTPS. Ask cPanel support to');
            $this->warn('   allow outbound 443 to '.$host.' ('.$ip.').');

            return self::FAILURE;
        }
        fclose($sock);
        $this->line('   TCP   :443 reachable');

        // 4. OAuth token.
        $this->info('4. Access token');
        $res = Http::withHeaders([
            'Authorization' => 'Basic '.base64_encode(
                config('mpesa.consumer_key').':'.config('mpesa.consumer_secret')
            ),
        ])->timeout(30)->get($base.'/oauth/v1/generate?grant_type=client_credentials');

        if (! $res->successful() || ! $res->json('access_token')) {
            $this->error('   FAIL  HTTP '.$res->status());
            $this->line('   '.$res->body());
            $this->warn('   Usually: consumer key/secret belong to a different app, or the');
            $this->warn('   app is a sandbox app while MPESA_ENV=live (or vice versa).');

            return self::FAILURE;
        }
        $this->line('   OK    token acquired');

        // 5. Real STK push, only when asked.
        $phone = $this->option('phone');
        if (! $phone) {
            $this->line('');
            $this->comment('Everything up to the STK push is healthy.');
            $this->comment('Re-run with --phone=07xxxxxxxx to send a real prompt.');

            return self::SUCCESS;
        }

        $this->info('5. STK push to '.$phone);
        try {
            $out = app(MpesaService::class)->stkPush($phone, 1, 'DIAG-'.date('His'), 'Diagnostic');
            $this->line('   OK    '.json_encode($out));
        } catch (\Throwable $e) {
            $this->error('   FAIL  '.$e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function mask(string $v): string
    {
        if ($v === '') {
            return '(EMPTY)';
        }

        return strlen($v) <= 8
            ? str_repeat('*', strlen($v))
            : substr($v, 0, 4).str_repeat('*', 6).substr($v, -4).'  (len '.strlen($v).')';
    }
}
