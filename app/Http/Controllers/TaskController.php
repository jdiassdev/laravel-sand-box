<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index()
    { 
        // authorize() chama TaskPolicy::viewAny()
        Gate::authorize('viewAny', Task::class);

        $tasks = Task::where('user_id', auth()->id())->get();

        return Inertia::render('Tasks/Index', ['tasks' => $tasks]);
    }

    public function store(Request $request)
    {
        // authorize() chama TaskPolicy::create()
        Gate::authorize('create', Task::class);

        $data = $request->validate(['title' => 'required|string|max:255']);

        Task::create([
            'user_id' => auth()->id(),
            'title'   => $data['title'],
        ]);

        return to_route('tasks.index');
    }

    public function update(Request $request, Task $task)
    {

        Gate::authorize('update', $task);

        $task->update($request->validate([
            'title'     => 'sometimes|string|max:255',
            'completed' => 'sometimes|boolean',
        ]));

        return to_route('tasks.index');
    }

    public function destroy(Task $task)
    {
        // authorize() chama TaskPolicy::delete()
        Gate::authorize('delete', $task);

        $task->delete();

        return to_route('tasks.index');
    }
}
