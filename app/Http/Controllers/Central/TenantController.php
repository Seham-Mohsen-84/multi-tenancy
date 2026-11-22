<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTenantRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Tenant::with('domains')->paginate(),
            'message' => 'Tenants retrieved successfully',
        ]);
    }

    public function store(StoreTenantRequest $request): JsonResponse
    {
        $data = $request->validated();

        $tenant = Tenant::create(['name' => $data['name']]);
        $tenant->createDomain($data['domain']);

        $tenant->run(function () use ($data): void {
            User::create($data['admin'])->assignRole('tenant_admin');
        });

        return response()->json([
            'data' => $tenant->load('domains'),
            'message' => 'Tenant created successfully',
        ], 201);
    }

    public function show(Tenant $tenant): JsonResponse
    {
        return response()->json([
            'data' => $tenant->load('domains'),
            'message' => 'Tenant retrieved successfully',
        ]);
    }

    public function update(Request $request, Tenant $tenant): JsonResponse
    {
        $tenant->update($request->validate(['name' => ['required', 'string', 'max:255']]));

        return response()->json([
            'data' => $tenant->load('domains'),
            'message' => 'Tenant updated successfully',
        ]);
    }

    public function destroy(Tenant $tenant): JsonResponse
    {
        $tenant->delete();

        return response()->json([
            'message' => 'Tenant deleted successfully',
        ]);
    }
}
