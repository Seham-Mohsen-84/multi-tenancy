<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use LogicException;
use RuntimeException;
use Stancl\Tenancy\Jobs\CreateDatabase;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        throw_if(tenancy()->initialized, LogicException::class, 'Seed demo tenants from the central context.');

        foreach ($this->fixtures() as $fixture) {
            $this->seedTenant($fixture);
        }
    }

    private function seedTenant(array $fixture): void
    {
        foreach ($fixture['domains'] as $hostname) {
            $owner = Domain::query()->where('domain', $hostname)->value('tenant_id');

            throw_if(
                in_array($hostname, config('tenancy.central_domains'), true)
                    || ($owner !== null && $owner !== $fixture['id']),
                LogicException::class,
                "Demo domain {$hostname} is already in use.",
            );
        }

        $tenant = Tenant::query()->firstOrNew(['id' => $fixture['id']], ['name' => $fixture['name']]);

        try {
            $this->provisionDatabase($tenant, $fixture['users']);
            tenancy()->initialize($tenant);

            DB::transaction(function () use ($fixture): void {
                $this->call(RoleSeeder::class);
                $this->callWith(UserSeeder::class, ['users' => $fixture['users']]);
            });
        } finally {
            tenancy()->end();
        }

        DB::transaction(function () use ($tenant, $fixture): void {
            foreach ($fixture['domains'] as $hostname) {
                $tenant->domains()->firstOrCreate(['domain' => $hostname]);
            }
        });
    }

    private function provisionDatabase(Tenant $tenant, array $users): void
    {
        $database = $tenant->database();
        $databaseName = $database->getName();
        $databaseExists = $database->manager()->databaseExists($databaseName);

        if (! $tenant->exists && ! $databaseExists) {
            // TenantCreated provisions the database, runs migrations, and seeds roles.
            $tenant->save();

            return;
        }

        if (! $tenant->exists) {
            $admin = collect($users)->firstWhere('role', 'tenant_admin');
            $this->restoreTenant($tenant, $databaseName, $admin['email']);
        }

        // Resume provisioning if a previous seed stopped after inserting the tenant.
        if (! $databaseExists) {
            app()->call([new CreateDatabase($tenant), 'handle']);
        }

        $exitCode = Artisan::call('tenants:migrate', ['--tenants' => [$tenant->getTenantKey()]]);
        throw_if($exitCode !== 0, RuntimeException::class, trim(Artisan::output()));
    }

    private function restoreTenant(Tenant $tenant, string $databaseName, string $adminEmail): void
    {
        $databaseColumn = $tenant->getColumnForQuery(Tenant::internalPrefix().'db_name');
        throw_if(
            Tenant::query()->where($databaseColumn, $databaseName)->exists(),
            LogicException::class,
            "Database {$databaseName} is already linked to a tenant.",
        );

        try {
            tenancy()->initialize($tenant);
            $connection = DB::connection('tenant');

            throw_unless(
                $connection->getSchemaBuilder()->hasColumn('users', 'email')
                    && $connection->table('users')->where('email', $adminEmail)->exists(),
                LogicException::class,
                "Database {$databaseName} exists without the expected demo admin {$adminEmail}.",
            );
        } finally {
            tenancy()->end();
        }

        // Register the existing database without starting the TenantCreated provisioning pipeline.
        $tenant->setInternal('db_name', $databaseName)->saveQuietly();
    }

    private function fixtures(): array
    {
        $onboardingTasks = [];

        foreach (range(1, 16) as $number) {
            $onboardingTasks["Review onboarding request #{$number}"] = $number % 2 === 0
                ? null
                : 'Confirm the contact details and review the requested access.';
        }

        return [
            [
                'id' => '5a11d4a7-0d7f-45ca-8da7-15fc06714a40',
                'name' => 'Acme Demo',
                'domains' => ['acme.demo.test', 'portal.acme.demo.test'],
                'users' => [
                    [
                        'name' => 'Acme Admin',
                        'email' => 'admin@acme.demo.test',
                        'role' => 'tenant_admin',
                        'verified' => true,
                        'tasks' => ['Review access requests' => 'Check the outstanding requests from the Acme team.'],
                    ],
                    [
                        'name' => 'Mona Ali',
                        'email' => 'member@demo.test',
                        'role' => 'member',
                        'verified' => true,
                        'tasks' => $onboardingTasks,
                    ],
                    [
                        'name' => 'Sara Hassan',
                        'email' => 'new.member@acme.demo.test',
                        'role' => 'member',
                        'verified' => false,
                        'tasks' => [],
                    ],
                ],
            ],
            [
                'id' => '7151bf1f-a1d0-46e5-9a3b-9d2dff27bc15',
                'name' => 'Orbit Demo',
                'domains' => ['orbit.demo.test'],
                'users' => [
                    [
                        'name' => 'Orbit Admin',
                        'email' => 'admin@orbit.demo.test',
                        'role' => 'tenant_admin',
                        'verified' => true,
                        'tasks' => ['Review access requests' => 'Check the outstanding requests from the Orbit team.'],
                    ],
                    [
                        'name' => 'Omar Salem',
                        'email' => 'member@demo.test',
                        'role' => 'member',
                        'verified' => true,
                        'tasks' => [
                            'Prepare the support schedule' => 'Draft the schedule for next week.',
                            'Archive resolved requests' => null,
                        ],
                    ],
                ],
            ],
            [
                'id' => 'f69325da-6109-40d5-a465-2f8e8128283d',
                'name' => 'Studio Demo',
                'domains' => ['studio.demo.test'],
                'users' => [
                    [
                        'name' => 'Studio Admin',
                        'email' => 'admin@studio.demo.test',
                        'role' => 'tenant_admin',
                        'verified' => true,
                        'tasks' => [],
                    ],
                ],
            ],
        ];
    }
}
