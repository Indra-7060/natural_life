<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Run with:  php spark db:seed DatabaseSeeder
 * Safe to re-run: content tables are refreshed, customers and orders are never touched.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        helper('site');
        $this->call(AccessSeeder::class);
        $this->call(SettingsSeeder::class);
        $this->call(ContentSeeder::class);
        $this->call(PagesSeeder::class);
        cache()->clean();
    }
}
