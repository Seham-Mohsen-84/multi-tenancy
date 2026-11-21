<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;

class StoreTenantRequest extends SaveDomainRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'name' => ['required', 'string', 'max:255'],
            'admin' => ['required', 'array:name,email,password'],
            'admin.name' => ['required', 'string', 'max:255'],
            'admin.email' => ['required', 'email', 'max:255'],
            'admin.password' => ['required', 'string', Password::min(8)],
        ];
    }
}
