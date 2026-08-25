<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One M-Pesa receipt may settle exactly one application.
 *
 * The application code refuses a code already claimed elsewhere, but that
 * check and the write that follows it are not atomic: two applications could
 * both claim the same receipt and both be confirmed if their results landed
 * together. A unique index is the only guard that holds regardless of timing,
 * and it is what stops one genuine SMS unlocking several applications.
 *
 * Unpaid rows hold NULL, and MySQL allows repeated NULLs in a unique index,
 * so only real receipts are constrained.
 */
return new class extends Migration
{
    public function up(): void
    {
        // An empty string is NOT null to a unique index, so two blank receipts
        // would collide. Normalise them first -- a blank receipt carries no
        // information, and this is the difference between the guard below
        // working and the index failing halfway through.
        DB::table('applications')->where('mpesa_receipt', '')->update(['mpesa_receipt' => null]);

        // Refuse to run rather than fail halfway with a driver-level error:
        // existing duplicates are a data question for a human, not something
        // a migration should resolve by guessing which row to keep.
        $duplicates = DB::table('applications')
            ->select('mpesa_receipt', DB::raw('COUNT(*) as total'))
            ->whereNotNull('mpesa_receipt')
            ->groupBy('mpesa_receipt')
            ->having('total', '>', 1)
            ->pluck('total', 'mpesa_receipt');

        if ($duplicates->isNotEmpty()) {
            $detail = $duplicates
                ->map(fn ($count, $receipt) => $receipt . ' (' . $count . ' applications)')
                ->implode(', ');

            throw new RuntimeException(
                'Cannot add a unique index: these M-Pesa receipts are already on more than one application -- '
                . $detail
                . '. Decide which application each receipt belongs to, clear it from the others, then re-run.'
            );
        }

        Schema::table('applications', function (Blueprint $table) {
            $table->unique('mpesa_receipt');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropUnique(['mpesa_receipt']);
        });
    }
};
