<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the authenticated user's tasks.
     */
    public function index()
    {
        // Only return tasks belonging to the logged-in user
        $tasks = Task::where('user_id', auth()->id())->get();
        return response()->json($tasks, 200);
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Inject the authenticated user's ID to fix the NOT NULL constraint
        $validatedData['user_id'] = auth()->id();

        $task = Task::create($validatedData);

        return response()->json($task, 201);
    }

    /**
     * Display the specified task if it belongs to the user.
     */
    public function show(Task $task)
    {
        // Prevent users from viewing other people's tasks
        if ($task->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized Access'], 403);
        }

        return response()->json($task, 200);
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, Task $task)
    {
        // Prevent users from updating other people's tasks
        if ($task->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized Action'], 403);
        }

        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $task->update($validatedData);

        return response()->json($task, 200);
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task)
    {
        // Prevent users from deleting other people's tasks
        if ($task->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized Action'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully'], 200);
    }
}