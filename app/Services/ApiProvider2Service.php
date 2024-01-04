<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\TaskServiceInterface;
use App\Models\Task;

class ApiProvider2Service implements TaskServiceInterface
{
    public $apiUrl = 'https://run.mocky.io/v3/7b0ff222-7a9c-4c54-9396-0df58e289143';
    public $apiDurationKey = 'estimated_duration';
    public $apiDiffucultyKey = 'value';

    public function createTasksFromData(array $tasksData): void
    {
        foreach ($tasksData as $taskData) {
            try {
                // logic to create tasks from the data received
                $exists = Task::where('slug', $taskData['id'])->first();
                if (!$exists) {
                    Task::create([
                        'slug' => $taskData['id'],
                        'difficulty' => $taskData[$this->apiDiffucultyKey],
                        'duration' => $taskData[$this->apiDurationKey],
                    ]);
                }
            } catch (\Exception $e) {
                // Log any exceptions that occur during task creation for a specific task
                Log::error('Error creating task with ID ' . $taskData['id'] . ': ' . $e->getMessage());
            }
        }
    }
}
