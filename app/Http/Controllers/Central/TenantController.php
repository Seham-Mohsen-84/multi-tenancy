<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTenantRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

class TenantController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Tenant::with('domains')->paginate());
    }

    public function store(StoreTenantRequest $request): JsonResponse
    {
        $data = $request->validated();
        $tenant = new Tenant(['name' => $data['name']]);

        try {
            $tenant->save();

            try {
                tenancy()->initialize($tenant);
                DB::transaction(function () use ($data): void {
                    User::create($data['admin'])->assignRole('tenant_admin');
                });
            } finally {
                tenancy()->end();
            }

            $tenant->createDomain($data['domain']);
        } catch (Throwable $exception) {
            tenancy()->end();

            if ($tenant->exists) {
                $database = $tenant->database();

                if ($database->manager()->databaseExists($database->getName())) {
                    $tenant->delete();
                } else {
                    $tenant->deleteQuietly();
                }
            }

            throw $exception;
        }

        return response()->json([
            "data"=>$tenant->load('domains'),
            "message"=>"Tenant created successfully",
            ],
            201);
    }

    public function show(Tenant $tenant): JsonResponse
    {
        return response()->json(["data"=>$tenant->load('domains')]);
    }

    public function update(Request $request, Tenant $tenant): JsonResponse
    {
        $tenant->update($request->validate(['name' => ['required', 'string', 'max:255']]));

        return response()->json($tenant->load('domains'));
    }

    public function destroy(Tenant $tenant): Response
    {
        $tenant->delete();

        return response()->noContent();
    }
}
