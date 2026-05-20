<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'filter' => 'sometimes|in:completed,pending',
        ]);

        $tasks = match($request->filter) {
            'completed' => Task::completed()->get(),
            'pending'   => Task::pending()->get(),
            default     => Task::all(),
        };

        return Inertia::render('Task', [
            'tasks'  => $tasks,
            'filter' => $request->filter,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'category' => 'sometimes|string|max:100',
        ]);

        Task::create($data);

        return to_route('tasks.index');
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'category'  => 'sometimes|string|max:100',
            'completed' => 'sometimes|boolean',
        ]);

        $task->update($data);

        return to_route('tasks.index');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return to_route('tasks.index');
    }
}
