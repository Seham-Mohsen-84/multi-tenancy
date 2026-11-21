<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use LogicException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        throw_unless(tenancy()->initialized, LogicException::class, 'Seed tenant roles in an initialized tenant.');

        $manageUsers = Permission::findOrCreate('manage users', 'api');
        $manageTasks = Permission::findOrCreate('manage all tasks', 'api');

        Role::findOrCreate('tenant_admin', 'api')->syncPermissions([$manageUsers, $manageTasks]);
        Role::findOrCreate('member', 'api');
    }
}
