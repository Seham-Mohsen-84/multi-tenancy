<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->accessibleTasks($request)->latest('id')->paginate(),
            'message' => 'Tasks retrieved successfully',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedTask($request);
        $data['user_id'] ??= $request->user()->getKey();
        $task = Task::create($data);

        return response()->json([
            'data' => $task,
            'message' => 'Task created successfully',
        ], 201);
    }

    public function show(Request $request, string $task): JsonResponse
    {
        return response()->json([
            'data' => $this->accessibleTasks($request)->findOrFail($task),
            'message' => 'Task retrieved successfully',
        ]);
    }

    public function update(Request $request, string $task): JsonResponse
    {
        $record = $this->accessibleTasks($request)->findOrFail($task);
        $record->update($this->validatedTask($request));

        return response()->json([
            'data' => $record,
            'message' => 'Task updated successfully',
        ]);
    }

    public function destroy(Request $request, string $task): JsonResponse
    {
        $this->accessibleTasks($request)->findOrFail($task)->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }

    private function accessibleTasks(Request $request): Builder
    {
        $user = $request->user();

        return $user->can('manage all tasks') ? Task::query() : $user->tasks()->getQuery();
    }

    private function validatedTask(Request $request): array
    {
        return $request->validate([
            'title' => [$request->isMethod('POST') ? 'required' : 'sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'user_id' => $request->user()->can('manage all tasks')
                ? ['sometimes', 'required', 'integer', Rule::exists('users', 'id')]
                : ['missing'],
        ]);
    }
}
