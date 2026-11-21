<?php

namespace App\Http\Middleware;

use Closure;
use Stancl\Tenancy\Contracts\TenantCouldNotBeIdentifiedException;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

class InitializeTenantRequest extends InitializeTenancyByDomain
{
    public function handle($request, Closure $next): mixed
    {
        try {
            return parent::handle($request, $next);
        } catch (TenantCouldNotBeIdentifiedException) {
            abort(404);
        } finally {
            $this->tenancy->end();
        }
    }
}
