<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use LogicException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        throw_if(tenancy()->initialized, LogicException::class, 'Seed demo data from the central context.');

        $this->call([
            AdminSeeder::class,
            TenantSeeder::class,
        ]);
    }
}
