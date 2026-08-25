<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Services\Mpesa\SettleStkPush;
use App\Services\MpesaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Settle STK payments the callback never reported.
 *
 * The browser-driven fallback only runs while the applicant has the page open.
 * Someone who pays and immediately closes the tab -- or whose phone finishes
 * the prompt after they gave up -- leaves a row stuck on `pending` even though
 * Safaricom knows the answer. This asks, on a schedule, with nobody watching.
 */
class MpesaSettlePendingStk extends Command
{
    protected $signature = 'mpesa:settle-pending-stk
        {--limit=25 : Most applications to check in one run}
        {--min-age=45 : Skip prompts younger than this many seconds}
        {--max-age=30 : Give up on prompts older than this many minutes}
        {--dry-run : Report what would be settled without changing anything}';

    protected $description = 'Query Safaricom for STK prompts whose callback never arrived, and settle them';

    public function handle(MpesaService $mpesa, SettleStkPush $settler): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $minAge = max(0, (int) $this->option('min-age'));
        $maxAge = max(1, (int) $this->option('max-age'));
        $dryRun = (bool) $this->option('dry-run');

        // updated_at is the moment the prompt was sent: store() writes the
        // checkout id then, and a row left pending is not touched again -- the
        // sweep only writes when it actually settles something.
        $pending = Application::query()
            ->where('payment_status', 'pending')
            ->whereNotNull('checkout_request_id')
            ->where('updated_at', '<=', now()->subSeconds($minAge))
            ->where('updated_at', '>=', now()->subMinutes($maxAge))
            ->orderBy('updated_at')
            ->limit($limit)
            ->get();

        if ($pending->isEmpty()) {
            $this->line('Nothing pending in the window.');

            return self::SUCCESS;
        }

        $this->line('Checking ' . $pending->count() . ' pending prompt(s).');

        $settled = 0;
        $failed = 0;

        foreach ($pending as $application) {
            try {
                $outcome = $mpesa->stkQuery((string) $application->checkout_request_id);
            } catch (\Throwable $e) {
                // Never let a query outage fail a payment; the next run retries.
                $this->warn('  ' . $application->reference . '  query failed: ' . $e->getMessage());
                continue;
            }

            if ($dryRun) {
                $this->line('  ' . $application->reference . '  would be: ' . $outcome['state']
                    . '  (' . $outcome['result_code'] . ' ' . $outcome['description'] . ')');
                continue;
            }

            $result = $settler->apply($application, $outcome);

            if ($result === SettleStkPush::PAID) {
                ++$settled;
                $this->info('  ' . $application->reference . '  settled as PAID');

                Log::info('Scheduled sweep settled an STK payment the callback never reported', [
                    'reference' => $application->reference,
                    'checkout_request_id' => $application->checkout_request_id,
                ]);
            } elseif ($result === SettleStkPush::FAILED) {
                ++$failed;
                $this->line('  ' . $application->reference . '  marked failed: ' . $outcome['description']);
            } else {
                $this->line('  ' . $application->reference . '  still ' . $outcome['state']);
            }
        }

        if (! $dryRun) {
            $this->line('Done. ' . $settled . ' paid, ' . $failed . ' failed.');
        }

        return self::SUCCESS;
    }
}
