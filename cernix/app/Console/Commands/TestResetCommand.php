<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        $this->newLine();
        $this->info('Done. Any mock_sis student can now be registered again.');
        $this->line('Tip: use a fresh TEST-RRR from storage/app/test-rrr-pool.json (or run php artisan db:seed --class=TestRrrSeeder to regenerate the list).');

        return self::SUCCESS;
    }
}
