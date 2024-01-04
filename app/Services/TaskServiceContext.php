<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TaskServiceContext
{
    private $taskService;

    public function __construct(TaskServiceInterface $taskService)
    {
        $this->taskService = $taskService;

        $response = Http::get($this->taskService->apiUrl);

        if ($response->successful()) {
            // API returns JSON, decode it
            $tasksData = $response->json();

            // Process $tasksData and create tasks
            $tasks = $this->taskService->createTasksFromData($tasksData);

            return $tasks;
        } else {
            // Handle the case where the API request was not successful
            Log::error('Failed to retrieve tasks from API. HTTP Status Code: ' . $response->status());
        }
    }
}
