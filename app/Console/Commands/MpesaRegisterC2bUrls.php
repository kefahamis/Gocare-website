<?php

//
// Usage:
//   php artisan mpesa:register-c2b-urls --dry-run          # print the payload, call nothing
//   php artisan mpesa:register-c2b-urls                    # register (asks for confirmation on live)
//   php artisan mpesa:register-c2b-urls --shortcode=9373479
//
// Config keys assumed (rename to match config/mpesa.php if yours differ):
//   mpesa.env            'live' | 'sandbox'
//   mpesa.consumer_key, mpesa.consumer_secret
//   mpesa.c2b.shortcode  store number for a till (falls back to mpesa.shortcode)
//   mpesa.c2b.token      random hex that forms part of the callback path
//
// Production registration is ONE-TIME per shortcode. Changing it later means
// deleting the URLs under Self Services > URL Management on the Daraja portal.

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class MpesaRegisterC2bUrls extends Command
{
    protected $signature = 'mpesa:register-c2b-urls
                            {--shortcode= : Override the shortcode (default: mpesa.c2b.shortcode)}
                            {--dry-run : Print the payload without calling Daraja}';

    protected $description = 'Register the C2B confirmation and validation URLs with Daraja';

    public function handle(): int
    {
        $env       = config('mpesa.env');
        $base      = $env === 'live' ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke';
        $shortcode = $this->option('shortcode') ?: (config('mpesa.c2b.shortcode') ?: config('mpesa.shortcode'));
        $token     = (string) config('mpesa.c2b.token');

        if ($token === '') {
            $this->error('MPESA_C2B_TOKEN is empty. Generate one: php -r \'echo bin2hex(random_bytes(24));\'');
            return self::FAILURE;
        }

        // route() in console uses APP_URL for the host, so these come out absolute.
        $payload = [
            'ShortCode'       => (string) $shortcode,
            'ResponseType'    => 'Completed',   // exactly this spelling and case
            'ConfirmationURL' => route('c2b.confirm', ['token' => $token]),
            'ValidationURL'   => route('c2b.validate', ['token' => $token]),
        ];

        $this->table(['key', 'value'], collect($payload)->map(fn ($v, $k) => [$k, $v])->values()->all());

        foreach (['ConfirmationURL', 'ValidationURL'] as $key) {
            if (! str_starts_with($payload[$key], 'https://') && $env === 'live') {
                $this->error("$key must be https in production.");
                return self::FAILURE;
            }
            if (preg_match('/m-?pesa|safaricom|exe|cmd|sql|query/i', $payload[$key])) {
                $this->error("$key contains a word Daraja rejects (mpesa, safaricom, exe, exec, cmd, sql, query).");
                return self::FAILURE;
            }
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run: nothing sent.');
            return self::SUCCESS;
        }

        if ($env === 'live' && ! $this->confirm(
            "Registering on PRODUCTION for shortcode $shortcode is a one-time call. Is the confirm URL deployed and answering 200?"
        )) {
            return self::SUCCESS;
        }

        $auth = Http::withBasicAuth(config('mpesa.consumer_key'), config('mpesa.consumer_secret'))
            ->get("$base/oauth/v1/generate", ['grant_type' => 'client_credentials']);

        if (! $auth->ok()) {
            $this->error('Token request failed: '.$auth->status().' '.$auth->body());
            return self::FAILURE;
        }

        $response = Http::withToken($auth->json('access_token'))
            ->acceptJson()
            ->post("$base/mpesa/c2b/v2/registerurl", $payload);

        $this->line($response->status().' '.$response->body());

        if ($response->ok() && (string) $response->json('ResponseCode') === '0') {
            $this->info('Registered. Next: pay a small amount to the till and watch the log for "C2B confirmation received".');
            return self::SUCCESS;
        }

        // Tell the failures apart: they lead to completely different next steps.
        $errorMessage = (string) ($response->json('errorMessage') ?? '');

        if (stripos($errorMessage, 'product assignment') !== false) {
            $this->error('C2B is NOT assigned to this shortcode.');
            $this->warn('Safaricom has to enable the C2B product for '.$shortcode.'. That is a');
            $this->warn('different (and more routine) request than the operator role that');
            $this->warn('Transaction Status is blocked on with ResultCode 21.');
        } elseif (stripos($errorMessage, 'already') !== false) {
            $this->error('URLs are ALREADY registered for this shortcode.');
            $this->warn('Delete them under Self Services > URL Management on the Daraja portal,');
            $this->warn('or find out where the existing ones point before building against new ones.');
        } elseif (stripos($errorMessage, 'shortcode') !== false) {
            $this->error('The shortcode was rejected.');
            $this->warn('For Buy Goods, try the other number: store '.config('mpesa.shortcode')
                .', till '.config('mpesa.till_number').'.');
            $this->warn('Re-run with --shortcode=<the other one>.');
        } else {
            $this->error('Registration not accepted. Quote the response above to Safaricom support.');
        }

        return self::FAILURE;
    }
}
