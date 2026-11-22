<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use LogicException;

class UserSeeder extends Seeder
{
    private const PASSWORD = 'TenantDemo123!';

    public function run(array $users): void
    {
        throw_unless(tenancy()->initialized, LogicException::class, 'Seed demo users in an initialized tenant.');

        foreach ($users as $fixture) {
            $user = User::query()->firstOrCreate(['email' => $fixture['email']], [
                'name' => $fixture['name'],
                'password' => self::PASSWORD,
                'email_verified_at' => $fixture['verified'] ? now() : null,
            ]);

            if ($user->wasRecentlyCreated) {
                $user->assignRole($fixture['role']);
            }

            foreach ($fixture['tasks'] as $title => $description) {
                $user->tasks()->firstOrCreate(['title' => $title], ['description' => $description]);
            }
        }
    }
}
