<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password'])]
class Admin extends Authenticatable
{
    use CentralConnection, HasApiTokens;

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }
}
