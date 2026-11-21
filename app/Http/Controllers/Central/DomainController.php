<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveDomainRequest;
use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DomainController extends Controller
{
    public function index(Tenant $tenant): JsonResponse
    {
        return response()->json($tenant->domains()->paginate());
    }

    public function store(SaveDomainRequest $request, Tenant $tenant): JsonResponse
    {
        return response()->json($tenant->createDomain($request->validated('domain')), 201);
    }

    public function show(Tenant $tenant, Domain $domain): JsonResponse
    {
        return response()->json($domain);
    }

    public function update(SaveDomainRequest $request, Tenant $tenant, Domain $domain): JsonResponse
    {
        $domain->update($request->validated());

        return response()->json($domain);
    }

    public function destroy(Tenant $tenant, Domain $domain): Response
    {
        DB::transaction(function () use ($tenant, $domain): void {
            Tenant::whereKey($tenant->getKey())->lockForUpdate()->firstOrFail();
            abort_if($tenant->domains()->count() < 2, 422, 'A tenant needs at least one domain.');
            $domain->delete();
        });

        return response()->noContent();
    }
}
