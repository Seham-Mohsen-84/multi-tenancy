<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            'admin@central.demo.test' => 'Demo Central Admin',
            'support@central.demo.test' => 'Demo Support Admin',
        ];

        foreach ($admins as $email => $name) {
            Admin::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => 'CentralDemo123!'],
            );
        }
    }
}
