<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Generates a pool of TEST- RRR numbers for local development.
 *
 * These RRR values are recognised by RemitaService when APP_ENV=local,
 * bypassing the real Remita API. Each value can only be used once per
 * session; run `php artisan test:reset` to make them available again.
 *
 * Usage:
 *   php artisan db:seed --class=TestRrrSeeder
 *   # Pool is written to storage/app/test-rrr-pool.json
 */
class TestRrrSeeder extends Seeder
{
    /** Fixed pool of 30 TEST- RRR numbers available for local testing. */
    public const POOL = [
        'TEST-RRR-0001', 'TEST-RRR-0002', 'TEST-RRR-0003', 'TEST-RRR-0004', 'TEST-RRR-0005',
        'TEST-RRR-0006', 'TEST-RRR-0007', 'TEST-RRR-0008', 'TEST-RRR-0009', 'TEST-RRR-0010',
        'TEST-RRR-0011', 'TEST-RRR-0012', 'TEST-RRR-0013', 'TEST-RRR-0014', 'TEST-RRR-0015',
        'TEST-RRR-0016', 'TEST-RRR-0017', 'TEST-RRR-0018', 'TEST-RRR-0019', 'TEST-RRR-0020',
        'TEST-RRR-0021', 'TEST-RRR-0022', 'TEST-RRR-0023', 'TEST-RRR-0024', 'TEST-RRR-0025',
        'TEST-RRR-0026', 'TEST-RRR-0027', 'TEST-RRR-0028', 'TEST-RRR-0029', 'TEST-RRR-0030',
    ];

    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command->error('TestRrrSeeder is only intended for the local environment.');
            return;
        }

        $path = storage_path('app/test-rrr-pool.json');
        file_put_contents($path, json_encode(self::POOL, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->command->info("Test RRR pool written to: {$path}");
        $this->command->newLine();
        $this->command->table(
            ['#', 'RRR Number'],
            array_map(
                fn (int $i, string $rrr) => [$i + 1, $rrr],
                array_keys(self::POOL),
                self::POOL,
            ),
        );

        $this->command->newLine();
        $this->command->line('Each RRR can only be used once. Run <info>php artisan test:reset</info> to clear used records.');
    }
}
