<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveTenantUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => User::paginate(),
            'message' => 'Users retrieved successfully',
        ]);
    }

    public function store(SaveTenantUserRequest $request): JsonResponse
    {
        $user = DB::transaction(function () use ($request): User {
            return User::create($request->safe()->except('role'))->assignRole($request->validated('role'));
        });

        return response()->json([
            'data' => $user,
            'message' => 'User created successfully',
        ], 201);
    }

    public function show(string $user): JsonResponse
    {
        return response()->json([
            'data' => User::findOrFail($user),
            'message' => 'User retrieved successfully',
        ]);
    }

    public function update(SaveTenantUserRequest $request, string $user): JsonResponse
    {
        $account = User::findOrFail($user);
        $role = $request->validated('role');

        abort_if($account->is($request->user()) && $role === 'member', 422, 'You cannot remove your own administrator role.');

        DB::transaction(function () use ($account, $request, $role): void {
            $account->update($request->safe()->except('role'));

            if ($role !== null) {
                $account->syncRoles($role);
            }
        });

        return response()->json([
            'data' => $account,
            'message' => 'User updated successfully',
        ]);
    }

    public function destroy(Request $request, string $user): JsonResponse
    {
        $account = User::findOrFail($user);
        abort_if($account->is($request->user()), 422, 'You cannot delete your own account.');

        $account->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}
