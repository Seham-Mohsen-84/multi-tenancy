<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->accessibleTasks($request)->latest('id')->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedTask($request);
        $data['user_id'] ??= $request->user()->getKey();

        return response()->json(Task::create($data), 201);
    }

    public function show(Request $request, string $task): JsonResponse
    {
        return response()->json($this->accessibleTasks($request)->findOrFail($task));
    }

    public function update(Request $request, string $task): JsonResponse
    {
        $record = $this->accessibleTasks($request)->findOrFail($task);
        $record->update($this->validatedTask($request));

        return response()->json($record);
    }

    public function destroy(Request $request, string $task): Response
    {
        $this->accessibleTasks($request)->findOrFail($task)->delete();

        return response()->noContent();
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
