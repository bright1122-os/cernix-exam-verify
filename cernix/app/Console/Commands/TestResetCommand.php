<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TestResetCommand extends Command
{
    protected $signature   = 'test:reset';
    protected $description = '[local only] Clear transaction tables so the registration→scan flow can be repeated without re-seeding';

    public function handle(): int
    {
        if (! app()->environment('local')) {
            $this->error('test:reset is only available when APP_ENV=local.');
            return self::FAILURE;
        }

        $this->warn('Clearing transaction tables (mock_sis, departments, exam_sessions and examiners are untouched)...');

        // Delete in FK-dependency order so no constraint is violated.
        DB::table('verification_logs')->delete();
        DB::table('qr_tokens')->delete();
        DB::table('payment_records')->delete();
        DB::table('students')->delete();

        $this->info('  verification_logs — cleared');
        $this->info('  qr_tokens         — cleared');
        $this->info('  payment_records   — cleared');
        $this->info('  students          — cleared');

        // Reset test_rrrs pool if it exists so all 100 RRRs become available again.
        if (Schema::hasTable('test_rrrs')) {
            DB::table('test_rrrs')->update(['status' => 'UNUSED', 'used_at' => null]);
            $count = DB::table('test_rrrs')->count();
            $this->info("  test_rrrs         — {$count} records reset to UNUSED");
        }

        $this->newLine();
        $this->info('Done. Any mock_sis student can now be registered again.');
        $this->line('Use any UNUSED RRR from <info>php artisan test:rrr-generate</info> to register a student.');

        return self::SUCCESS;
    }
}
