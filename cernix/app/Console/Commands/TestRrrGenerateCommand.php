<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TestRrrGenerateCommand extends Command
{
    protected $signature   = 'test:rrr-generate';
    protected $description = '[local only] Create or refresh the test_rrrs pool (100 TEST-RRR-0001…0100 entries)';

    private const COUNT          = 100;
    private const EXPECTED_AMOUNT = 10000.00;

    public function handle(): int
    {
        if (! app()->environment('local')) {
            $this->error('test:rrr-generate is only available when APP_ENV=local.');
            return self::FAILURE;
        }

        $this->ensureTableExists();

        $rows = $this->buildPool();

        // insertOrIgnore skips any rrr_number that already exists,
        // preserving USED status for records that have been consumed.
        DB::table('test_rrrs')->insertOrIgnore($rows);

        $total  = DB::table('test_rrrs')->count();
        $unused = DB::table('test_rrrs')->where('status', 'UNUSED')->count();
        $used   = DB::table('test_rrrs')->where('status', 'USED')->count();

        $this->info("test_rrrs pool ready — {$total} total / {$unused} UNUSED / {$used} USED");
        $this->newLine();

        $this->table(
            ['RRR Number', 'Expected Amount', 'Status'],
            DB::table('test_rrrs')
                ->orderBy('rrr_number')
                ->get(['rrr_number', 'expected_amount', 'status'])
                ->map(fn ($r) => [
                    $r->rrr_number,
                    '₦' . number_format((float) $r->expected_amount, 2),
                    $r->status,
                ])
                ->all(),
        );

        $this->newLine();
        $this->line('Use any <info>UNUSED</info> RRR in the Student Portal. Run <info>php artisan test:reset</info> to restore all records to UNUSED.');

        return self::SUCCESS;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function ensureTableExists(): void
    {
        if (Schema::hasTable('test_rrrs')) {
            return;
        }

        Schema::create('test_rrrs', function (Blueprint $table) {
            $table->string('rrr_number')->primary();
            $table->decimal('expected_amount', 10, 2)->default(self::EXPECTED_AMOUNT);
            $table->enum('status', ['UNUSED', 'USED'])->default('UNUSED');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('used_at')->nullable();
        });

        $this->info('Created test_rrrs table.');
    }

    private function buildPool(): array
    {
        return array_map(
            fn (int $n) => [
                'rrr_number'      => sprintf('TEST-RRR-%04d', $n),
                'expected_amount' => self::EXPECTED_AMOUNT,
                'status'          => 'UNUSED',
                'created_at'      => now(),
                'used_at'         => null,
            ],
            range(1, self::COUNT),
        );
    }
}
