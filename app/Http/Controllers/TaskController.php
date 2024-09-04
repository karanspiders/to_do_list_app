<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::all();
        return view('index', compact('tasks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'task' => 'required|unique:tasks,task',
        ]);

        $task = Task::create([
            'task' => $validated['task'],
        ]);

        return response()->json($task);
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'task' => 'required|unique:tasks,task,' . $task->id,
            'status' => 'sometimes|boolean',
        ]);

        $task->task = $validated['task'];
        if ($request->has('status')) {
            $task->status = $validated['status'];
        }
        $task->save();

        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json(['success' => true]);
    }

    public function showAll()
    {
        $tasks = Task::all();
        return response()->json($tasks);
    }
}
