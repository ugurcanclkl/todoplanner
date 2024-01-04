<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Schedule;
use App\Models\Developer;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function renderTasks(): View
    {
        $tasks = Task::whereNotNull('developer_id')
        ->with('developer', 'schedule')
        ->get()
        ->sortBy('developer.experience')
        ->groupBy('schedule.week');

        return view('tasks', ['tasks' => $tasks]);
    }
}
