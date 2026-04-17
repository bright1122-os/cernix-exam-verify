<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Populates the test_rrrs pool for local development.
 *
 * Delegates to test:rrr-generate so table creation, idempotency
 * logic, and output are all defined in one place.
 *
 * Usage:
 *   php artisan db:seed --class=TestRrrSeeder
 *   # or
 *   php artisan test:rrr-generate
 */
class TestRrrSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command->error('TestRrrSeeder is only intended for the local environment.');
            return;
        }

        $this->command->call('test:rrr-generate');
    }
}
