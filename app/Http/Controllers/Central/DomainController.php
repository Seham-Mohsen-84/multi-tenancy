<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveDomainRequest;
use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DomainController extends Controller
{
    public function index(Tenant $tenant): JsonResponse
    {
        return response()->json([
            'data' => $tenant->domains()->paginate(),
            'message' => 'Domains retrieved successfully',
        ]);
    }

    public function store(SaveDomainRequest $request, Tenant $tenant): JsonResponse
    {
        $domain = $tenant->createDomain($request->validated('domain'));

        return response()->json([
            'data' => $domain,
            'message' => 'Domain created successfully',
        ], 201);
    }

    public function show(Tenant $tenant, Domain $domain): JsonResponse
    {
        return response()->json([
            'data' => $domain,
            'message' => 'Domain retrieved successfully',
        ]);
    }

    public function update(SaveDomainRequest $request, Tenant $tenant, Domain $domain): JsonResponse
    {
        $domain->update($request->validated());

        return response()->json([
            'data' => $domain,
            'message' => 'Domain updated successfully',
        ]);
    }

    public function destroy(Tenant $tenant, Domain $domain): JsonResponse
    {
        DB::transaction(function () use ($tenant, $domain): void {
            Tenant::whereKey($tenant->getKey())->lockForUpdate()->firstOrFail();
            abort_if($tenant->domains()->count() < 2, 422, 'A tenant needs at least one domain.');
            $domain->delete();
        });

        return response()->json([
            'message' => 'Domain deleted successfully',
        ]);
    }
}
