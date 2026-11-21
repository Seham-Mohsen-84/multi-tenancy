<?php

namespace App\Http\Requests;

use App\Models\Domain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveDomainRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('domain'))) {
            $this->merge(['domain' => Str::lower($this->input('domain'))]);
        }
    }

    public function rules(): array
    {
        $domain = $this->route('domain');
        $unique = Rule::unique('domains', 'domain');

        if ($domain instanceof Domain) {
            $unique->ignore($domain);
        }

        return [
            'domain' => [
                'bail', 'required', 'string', 'max:253',
                'regex:/\A(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\z/',
                Rule::notIn(config('tenancy.central_domains')),
                $unique,
            ],
        ];
    }
}
