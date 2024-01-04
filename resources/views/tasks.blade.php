<!DOCTYPE html>
<html>
<head>
    <title>Task Report</title>
    <!-- Include any necessary stylesheets or scripts here -->
</head>
<body>

<h2>Task Report</h2>

@php
    $sortedWeeks = $tasks->keys()->sort();
@endphp

<div style="display: flex;  width:100%; flex-wrap:wrap; gap: 20px;">
@foreach ($sortedWeeks as $week)
<div style="display: flex;  width:100%; flex-wrap:wrap; gap: 20px;">
    <h3>Week {{ $week }}</h3>


    @foreach ($tasks[$week]->groupBy('developer.title') as $developer => $developerTasks)

    <div style="display: flex; flex-direction: column;">
    <h4>Developer: {{ $developer }}</h4>
    
    <table border="1">
        <thead>
            <tr>
                <th>Task</th>
                <th>Duration</th>
                <th>Difficulty</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($developerTasks as $task)
            <tr>
                <td>{{ $task->slug }}</td>
                <td>{{ $task->duration }}</td>
                <td>{{ $task->difficulty }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
        @endforeach
        </div>
        @endforeach
</div>
    
</body>
</html>