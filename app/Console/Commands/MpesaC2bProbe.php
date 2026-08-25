<?php

namespace App\Console\Commands;

use App\Services\MpesaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Find out whether this till can register C2B URLs, before any code is written
 * around the assumption that it can.
 *
 * C2B registration authenticates with the consumer key and secret alone -- no
 * initiator, no security credential -- so it is not blocked by the operator
 * permission that currently fails Transaction Status with ResultCode 21. The
 * open question is only whether the product is assigned to this shortcode,
 * and Safaricom answers that in one call.
 */
class MpesaC2bProbe extends Command
{
    protected $signature = 'mpesa:c2b-probe
        {--confirmation= : Confirmation URL to register (default: /mpesa/c2b/confirmation on the callback host)}
        {--validation= : Validation URL to register (default: /mpesa/c2b/validation on the callback host)}
        {--shortcode= : Override the shortcode to register against}
        {--response-type=Completed : Completed (accept when validation is unreachable) or Cancelled}
        {--version=1 : registerurl API version, 1 or 2}
        {--force : Register without asking for confirmation first}';

    protected $description = 'Ask Safaricom whether C2B URL registration is available for this shortcode';

    public function handle(MpesaService $mpesa): int
    {
        $live = in_array(strtolower((string) config('mpesa.env')), ['production', 'live'], true);
        $base = $live ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke';

        $version = $this->option('version') === '2' ? 'v2' : 'v1';

        // For Buy Goods the C2B shortcode is the store / head office number,
        // the same one the STK password is built from -- not the till.
        $shortcode = (string) ($this->option('shortcode') ?: config('mpesa.shortcode'));

        // Default the URLs onto the host Safaricom already reaches for the STK
        // callback: that one is known good, so a typo in APP_URL (which is
        // currently missing its scheme) cannot leak into this.
        $callback = (string) config('mpesa.callback_url');
        $parts = parse_url($callback);
        $origin = isset($parts['scheme'], $parts['host']) ? $parts['scheme'] . '://' . $parts['host'] : '';

        $confirmation = (string) ($this->option('confirmation') ?: $origin . '/mpesa/c2b/confirmation');
        $validation = (string) ($this->option('validation') ?: $origin . '/mpesa/c2b/validation');

        $responseType = $this->option('response-type') === 'Cancelled' ? 'Cancelled' : 'Completed';

        $this->line('');
        $this->info('1. What would be registered');
        $this->table(['key', 'value'], [
            ['env', ($live ? 'live' : 'sandbox') . '  -> ' . $base],
            ['endpoint', '/mpesa/c2b/' . $version . '/registerurl'],
            ['ShortCode', $shortcode],
            ['ResponseType', $responseType],
            ['ConfirmationURL', $confirmation],
            ['ValidationURL', $validation],
        ]);

        if ('' === $origin) {
            $this->error('   FAIL  Could not derive a host from MPESA_CALLBACK_URL: ' . ($callback ?: '(empty)'));
            $this->warn('   Pass --confirmation= and --validation= explicitly.');

            return self::FAILURE;
        }

        foreach (['ConfirmationURL' => $confirmation, 'ValidationURL' => $validation] as $name => $url) {
            if (! str_starts_with($url, 'https://')) {
                $this->error('   FAIL  ' . $name . ' must be an absolute https:// URL. Got: ' . ($url ?: '(empty)'));

                return self::FAILURE;
            }
        }

        // 2. These endpoints do not exist yet -- that is expected on a probe,
        // but say so plainly, because a registered URL that 404s means
        // Safaricom pushes real payments into the void.
        $this->info('2. Do those URLs answer yet?');
        foreach (['ConfirmationURL' => $confirmation, 'ValidationURL' => $validation] as $name => $url) {
            try {
                $probe = Http::timeout(15)->acceptJson()->post($url, ['TransID' => 'PROBE']);
                $code = $probe->status();

                if ($code === 200) {
                    $this->line('   OK    ' . $name . ' answers 200');
                } else {
                    $this->warn('   NOTE  ' . $name . ' answers HTTP ' . $code . ' -- build it before real payments arrive.');
                }
            } catch (\Throwable $e) {
                $this->warn('   NOTE  ' . $name . ' is not reachable yet: ' . $e->getMessage());
            }
        }

        $this->info('3. Register');

        if (! $this->option('force')) {
            $this->warn('   This tells Safaricom where to send live payment notifications for');
            $this->warn('   shortcode ' . $shortcode . '. Registering a URL that does not answer means');
            $this->warn('   real confirmations are lost. Re-run with --force once you are ready,');
            $this->warn('   or pass --confirmation=/--validation= to point somewhere else.');
            $this->line('');
            $this->comment('   Nothing was sent.');

            return self::SUCCESS;
        }

        try {
            $token = $mpesa->getAccessToken();
        } catch (\Throwable $e) {
            $this->error('   FAIL  Could not get an access token: ' . $e->getMessage());

            return self::FAILURE;
        }

        $response = Http::withToken($token)->post($base . '/mpesa/c2b/' . $version . '/registerurl', [
            'ShortCode' => $shortcode,
            'ResponseType' => $responseType,
            'ConfirmationURL' => $confirmation,
            'ValidationURL' => $validation,
        ]);

        $body = $response->json();
        $this->line('   HTTP ' . $response->status() . '  ' . trim($response->body()));
        $this->line('');

        $this->info('4. What that means');

        $errorMessage = is_array($body) ? (string) ($body['errorMessage'] ?? '') : '';
        $description = is_array($body) ? (string) ($body['ResponseDescription'] ?? '') : '';

        if ($response->successful() && stripos($description, 'success') !== false) {
            $this->line('   C2B registration IS available on this shortcode.');
            $this->line('   Next: build the confirmation endpoint and a payments table, then');
            $this->line('   look codes up locally instead of querying Transaction Status.');

            return self::SUCCESS;
        }

        // The failures worth telling apart, because they lead to different asks.
        if (stripos($errorMessage, 'product assignment') !== false) {
            $this->error('   C2B is NOT assigned to this shortcode.');
            $this->warn('   Safaricom has to enable the C2B product for ' . $shortcode . '. This is a');
            $this->warn('   different (and more routine) request than the operator role that');
            $this->warn('   Transaction Status is blocked on.');
        } elseif (stripos($errorMessage, 'already') !== false) {
            $this->line('   URLs are ALREADY registered for this shortcode.');
            $this->warn('   Safaricom generally will not let you re-register without their help,');
            $this->warn('   so find out where the existing URLs point before building against new ones.');
        } elseif (stripos($errorMessage, 'shortcode') !== false) {
            $this->error('   The shortcode was rejected.');
            $this->warn('   For Buy Goods, register the store / head office number (' . config('mpesa.shortcode') . '),');
            $this->warn('   not the till customers pay to (' . config('mpesa.till_number') . ').');
            $this->warn('   Try: --shortcode=' . config('mpesa.till_number'));
        } elseif ($version === 'v1') {
            $this->warn('   Unrecognised failure. Some accounts are on the v2 endpoint --');
            $this->warn('   re-run with --version=2 before concluding anything.');
        } else {
            $this->warn('   Unrecognised failure. Quote the HTTP body above to Safaricom support.');
        }

        return self::FAILURE;
    }
}
