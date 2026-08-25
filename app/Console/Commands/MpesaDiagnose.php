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
        {--type= : Override paybill|till}
        {--code= : Query this real M-Pesa confirmation code with Transaction Status}
        {--reference= : Occasion sent with --code (defaults to DIAG-HHMMSS)}';

    protected $description = 'Check every stage of the M-Pesa STK push and manual-verification paths';

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

        if ($phone) {
            $this->info('5. STK push to '.$phone);
            try {
                $out = app(MpesaService::class)->stkPush($phone, 1, 'DIAG-'.date('His'), 'Diagnostic');
                $this->line('   OK    '.json_encode($out));
            } catch (\Throwable $e) {
                $this->error('   FAIL  '.$e->getMessage());

                return self::FAILURE;
            }
        } else {
            $this->info('5. STK push');
            $this->line('   SKIP  re-run with --phone=07xxxxxxxx to send a real prompt');
        }

        // 6. The manual-payment path: confirming a typed code with the
        // Transaction Status API. Everything above is shared with the STK push,
        // so a healthy push proves nothing about this.
        $this->info('6. Transaction Status config');

        $status = config('mpesa.status');
        $cert = (string) $status['certificate_path'];

        // A pre-encrypted credential short-circuits securityCredential() before
        // it ever opens the certificate, so an unreadable file is not a fault
        // in that case -- reporting it as one sent the last debugging session
        // hunting for a certificate that was never going to be used.
        $preEncrypted = filled($status['security_credential'] ?? null);

        if ($preEncrypted) {
            $certNote = $cert.'   (not needed -- using MPESA_STATUS_SECURITY_CREDENTIAL)';
        } else {
            $certNote = $cert.(is_readable($cert) ? '   (readable)' : '   (NOT READABLE)');
        }

        $this->table(['key', 'value'], [
            ['initiator',        (string) ($status['initiator'] ?: '(EMPTY)')],
            ['initiator_pass',   $this->mask((string) $status['initiator_password'])],
            ['security_cred',    $this->mask((string) $status['security_credential'])],
            ['certificate',      $certNote],
            ['identifier_type',  (string) $status['identifier_type']],
            ['party_a',          (string) config('mpesa.shortcode').'  (store / head office number)'],
            ['result_url',       (string) $status['result_url']],
            ['timeout_url',      (string) $status['timeout_url']],
        ]);

        // The one way the token guard bites: set, but the URLs still point at
        // the unguarded paths. Safaricom would POST verdicts into a 404 and
        // every manual verification would hang for ever.
        $statusToken = (string) ($status['token'] ?? '');

        if ($statusToken !== '') {
            $mismatched = [];

            foreach (['result_url' => 'MPESA_STATUS_RESULT_URL', 'timeout_url' => 'MPESA_STATUS_TIMEOUT_URL'] as $key => $env) {
                if (! str_contains((string) $status[$key], $statusToken)) {
                    $mismatched[] = $env;
                }
            }

            if ($mismatched !== []) {
                $this->error('   FAIL  MPESA_STATUS_TOKEN is set, but these do not carry it: ' . implode(', ', $mismatched));
                $this->warn('   Safaricom would POST results to the old unguarded path and get a 404.');
                $this->warn('   Point them at /payments/status/<token>/result and /timeout, then:');
                $this->warn('   php artisan config:clear');

                return self::FAILURE;
            }

            $this->line('   OK    Result/Timeout URLs carry the status token');
        }

        $mpesa = app(MpesaService::class);

        // The same guard confirmManualPayment() consults. Failing it is not an
        // error -- it is the documented "record the code, verify nothing"
        // fallback -- but it is never what you want on a live site.
        if (! $mpesa->statusQueryConfigured()) {
            $this->warn('   OFF   typed codes are only recorded, never verified.');
            $this->warn('   Needs MPESA_STATUS_INITIATOR, an initiator password or a');
            $this->warn('   pre-encrypted MPESA_STATUS_SECURITY_CREDENTIAL, and an https://');
            $this->warn('   MPESA_STATUS_RESULT_URL. Then: php artisan config:clear');

            return self::SUCCESS;
        }

        // Build the credential through the service itself rather than repeating
        // its logic: a missing or malformed certificate has to fail loudly here,
        // because in the request path that exception is swallowed and the
        // applicant is simply told their payment was recorded.
        $this->info('7. Security credential');
        try {
            $credential = (new \ReflectionMethod(MpesaService::class, 'securityCredential'))->invoke($mpesa);
            $this->line('   OK    encrypted, '.strlen($credential).' chars');
        } catch (\Throwable $e) {
            $this->error('   FAIL  '.$e->getMessage());

            if ($preEncrypted) {
                $this->warn('   MPESA_STATUS_SECURITY_CREDENTIAL is set, so the certificate is not');
                $this->warn('   involved -- the supplied value itself was rejected.');
            } else {
                $this->warn('   Put the Safaricom production certificate at '.$cert);
                $this->warn('   or set MPESA_STATUS_SECURITY_CREDENTIAL to a pre-encrypted value.');
                $this->warn('   A ~344-character value ending in "==" is already encrypted: it');
                $this->warn('   belongs in MPESA_STATUS_SECURITY_CREDENTIAL, not in the password.');
            }

            return self::FAILURE;
        }

        // 8. The result only ever arrives on the ResultURL, so prove it answers
        // before blaming Safaricom for a verdict that never came back. An
        // unmatched conversation id is accepted and ignored by design.
        $this->info('8. Result URL reachability');
        try {
            $probe = Http::timeout(20)->acceptJson()->post((string) $status['result_url'], [
                'Result' => ['OriginatorConversationID' => 'diagnostic-probe-'.date('His')],
            ]);

            if ($probe->status() === 200) {
                $this->line('   OK    HTTP 200 '.trim($probe->body()));
            } else {
                $this->error('   FAIL  HTTP '.$probe->status().' -- Safaricom cannot deliver the result here.');
                $this->line('   '.trim($probe->body()));
                $this->warn('   A 419 means the CSRF exemption is missing; a 404 means the URL');
                $this->warn('   and the route in routes/web.php disagree.');

                return self::FAILURE;
            }
        } catch (\Throwable $e) {
            $this->error('   FAIL  '.$e->getMessage());

            return self::FAILURE;
        }

        // 9. A real query, only when asked. Needs a genuine confirmation code
        // from a payment that actually hit the till.
        $code = $this->option('code');

        if (! $code) {
            $this->line('');
            $this->comment('Everything up to the status query is healthy.');
            $this->comment('Pay the till KES 1, then re-run with --code=<code from the SMS>.');

            return self::SUCCESS;
        }

        $this->info('9. Transaction Status query for '.strtoupper($code));
        try {
            $out = $mpesa->transactionStatus($code, $this->option('reference') ?: 'DIAG-'.date('His'));
            $this->line('   OK    '.json_encode($out));
        } catch (\Throwable $e) {
            $this->error('   FAIL  '.$e->getMessage());
            $this->warn('   "Invalid initiator information" means the operator name or password');
            $this->warn('   is wrong, or the operator lacks the Transaction Status Query role.');
            $this->warn('   An invalid-party error means IdentifierType and PartyA disagree:');
            $this->warn('   try MPESA_STATUS_IDENTIFIER_TYPE=2 with the till number instead.');

            return self::FAILURE;
        }

        $this->line('');
        $this->comment('Daraja only acknowledged. The verdict arrives asynchronously on the');
        $this->comment('ResultURL -- watch for it with:');
        $this->comment('   tail -f storage/logs/laravel.log | grep -i "Transaction Status"');

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
