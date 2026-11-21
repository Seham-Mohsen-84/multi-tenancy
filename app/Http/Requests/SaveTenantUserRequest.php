<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SaveTenantUserRequest extends FormRequest
{
    public function rules(): array
    {
        $creating = $this->isMethod('POST');
        $presence = $creating ? 'required' : 'sometimes';

        return [
            'name' => [$presence, 'required', 'string', 'max:255'],
            'email' => [$presence, 'required', 'email', 'max:255', Rule::unique('users')->ignore($this->route('user'))],
            'password' => [$presence, 'required', 'string', Password::min(8)],
            'role' => [$presence, 'required', Rule::in(['tenant_admin', 'member'])],
        ];
    }
}
