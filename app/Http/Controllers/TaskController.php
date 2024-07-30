<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::all();
        if (request()->ajax()) {
            return response()->json(['tasks' => $tasks]);
        }
        return view('tasks.index', compact('tasks'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'task' => 'required|string|max:255|unique:tasks,task',
            ]);
    
            $task = Task::create([
                'task' => $request->task,
            ]);
    
            if ($request->ajax()) {
                return response()->json(['task' => $task]);
            }
    
            return redirect()->route('tasks.index');
    
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
    
            return redirect()->route('tasks.index')
                ->withErrors($e->errors())
                ->withInput();
        }    
    }

    public function update(Request $request, Task $task)
    {
        try {
            $task->status = $request->input('taskStatus', 1);
            $task->save();
        
            if ($request->ajax()) {
                return response()->json(['message' => 'Task status updated successfully!', 'task' => $task]);
            }
        
            return redirect()->route('tasks.index');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return redirect()->route('tasks.index')
                ->with('error', 'An error occurred while updating the task.');
        }
    }

    public function destroy(Task $task)
    {
        try {
            $task->delete();

            if (request()->ajax()) {
                return response()->json(['message' => 'Task deleted successfully']);
            }

            return redirect()->route('tasks.index');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return redirect()->route('tasks.index')
                ->with('error', 'An error occurred while deleting the task.');
        }
    }
}
