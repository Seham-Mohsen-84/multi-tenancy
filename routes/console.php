<?php

use App\Models\Admin;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:create-admin {email} {--name=Central Admin}', function (): int {
    $data = Validator::make([
        'email' => $this->argument('email'),
        'name' => $this->option('name'),
        'password' => $this->secret('Password'),
    ], [
        'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
        'name' => ['required', 'string', 'max:255'],
        'password' => ['required', 'string', Password::min(8)],
    ])->validate();

    Admin::create($data);
    $this->info('Central admin created.');

    return 0;
})->purpose('Create a central admin for the SaaS API');
