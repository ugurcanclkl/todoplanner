<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\TaskServiceInterface;
use App\Models\Task;

class ApiProvider1Service implements TaskServiceInterface
{
    public $apiUrl = 'https://run.mocky.io/v3/27b47d79-f382-4dee-b4fe-a0976ceda9cd';
    public $apiDurationKey = 'sure';
    public $apiDiffucultyKey = 'zorluk';

    
    public function createTasksFromData(array $tasksData): void
    {
        foreach ($tasksData as $taskData) {
            try {
                // logic to create tasks from the data received
                Task::create([
                    'slug' => $taskData['id'],
                    'difficulty' => $taskData[$this->apiDiffucultyKey],
                    'duration' => $taskData[$this->apiDurationKey],
                ]);
            } catch (\Exception $e) {
                // Log any exceptions that occur during task creation for a specific task
                Log::error('Error creating task with ID ' . $taskData['id'] . ': ' . $e->getMessage());
            }
        }
    }
}
