<?php

namespace App\Services;

interface TaskServiceInterface
{
    public function createTasksFromData(array $tasksData): void;
}
