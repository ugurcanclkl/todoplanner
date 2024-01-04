<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\Schedule;
use App\Models\Task;
use App\Models\Developer;
use App\Events\AssignTasksEvent;

class AssignTasksListener
{
    public function handle(object $event): void
    {
        // Get the current week from the event object
        $currentWeek = $event->currentWeek;

        // Log the start of the AssignTasksListener with the current week
        Log::debug('AssignTasksListener Started', [
            'week' => $currentWeek,
        ]);

        // Get schedules with remaining work for the current week
        $schedules = $this->getSchedulesWithRemainingWork($currentWeek);

        // If there are no schedules, create schedules for developers for the current week
        if ($schedules->isEmpty()) {
            $schedules = $this->createSchedulesForDevelopers($currentWeek);
        }

        // Get unassigned tasks
        $unassignedTasks = $this->getUnassignedTasks();

        // Get the total duration of tasks
        $totalDuration = $this->getTotalDuration();

        // Calculate developer working time based on minimum difficulty of tasks
        $devWorkingTimeByMinimumDifficulty = $this->calculateDeveloperWorkingTime();

        // Check if task assignment is possible
        if ($this->isTaskAssignmentPossible($totalDuration, $devWorkingTimeByMinimumDifficulty)) {
            // If possible, assign tasks for the current week and return
            $this->assignTasksForCurrentWeek($unassignedTasks, $schedules);
            return;
        }

        // If task assignment is not possible, assign tasks to developers
        $this->assignTasksToDevelopers($schedules, $unassignedTasks, $currentWeek);

        // Increment the current week
        $currentWeek++;

        // If there are still unassigned tasks, trigger AssignTasksEvent for the next week
        if ($unassignedTasks->isNotEmpty()) {
            event(new AssignTasksEvent($currentWeek));
        }
    }

    private function getSchedulesWithRemainingWork(int $currentWeek): Collection
    {
        // Retrieve schedules with remaining work for the current week
        return Schedule::where('remaining_work_time', '>', 0)->where('week', $currentWeek)->get();
    }

    private function createSchedulesForDevelopers(int $currentWeek): Collection
    {
        // Create schedules for developers for the current week
        return Developer::get()->map(function (Developer $developer) use ($currentWeek) {
            return $developer->schedules()->create([
                'week'                => $currentWeek,
                'remaining_work_time' => $developer->weekly_working_time,
            ]);
        });
    }

    private function getUnassignedTasks(): Collection
    {
        // Retrieve unassigned tasks, ordered by duration in descending order
        return Task::whereNull('developer_id')->orderBy('duration', 'desc')->get();
    }

    private function getTotalDuration(): int
    {
        // Calculate the total duration of unassigned tasks based on difficulty
        $totalDurationsByDifficulty = Task::whereNull('developer_id')
            ->select('difficulty', DB::raw('sum(duration * difficulty) as total'))
            ->orderByDesc('total')
            ->groupBy('difficulty')
            ->get();

        return $totalDurationsByDifficulty->sum('total');
    }

    private function calculateDeveloperWorkingTime(): int
    {
        // Calculate the total working time for all developers based on experience and weekly working time
        return Developer::get()->sum(fn (Developer $developer) => $developer->experience * $developer->weekly_working_time);
    }

    private function isTaskAssignmentPossible(int $totalDuration, int $devWorkingTime): bool
    {
        // Check if it's possible to assign tasks based on the ratio of total task duration to developer working time
        return $totalDuration / $devWorkingTime < 1;
    }

    private function assignTasksForCurrentWeek(Collection $unassignedTasks, Collection $schedules): void
    {
        // Check if it's possible to assign tasks based on the ratio of total task duration to developer working time
        $this->assignCurrentWeekTasks($unassignedTasks, $schedules);
    }

    private function assignTasksToDevelopers(Collection $schedules, Collection $unassignedTasks, int $currentWeek): void
    {
        $schedules->loadMissing('developer')
            ->sortByDesc('developer.experience')
            ->each(function (Schedule $schedule) use (&$unassignedTasks, $currentWeek): void {

                $developer = $schedule->developer;
                $experience = $developer->experience;

                // Iterate until the schedule's remaining work time is exhausted
                while ($schedule->remaining_work_time > 0) {
                    $workTime = $developer->experience / $experience * $schedule->remaining_work_time;

                    // Find an assignable task for the developer
                    $task = $this->findAssignableTask($unassignedTasks, $experience, $workTime);

                    // Log debug information during task assignment
                    $this->logDebugInfoDuringTaskAssignment($task, $experience, $schedule, $workTime);

                    // If no task is found, get the longest task by experience
                    if (!$task) {
                        $task = $this->getLongestTaskByExperience($experience);

                        // Log that a task was not found for the experience difficulty
                        $this->logTaskNotFoundForExperience($task, $currentWeek);

                        // If still no task is found, break out of the loop
                        if (!$task) {
                            break;
                        }

                        // Update the experience difficulty based on the new task
                        $experience = $task->difficulty;

                        continue;
                    }

                    // Update schedule and task assignment
                    $this->updateScheduleAndTaskAssignment($schedule, $task, $unassignedTasks);
                }
            });
    }

    private function findAssignableTask(Collection $unassignedTasks, int $experience, float $workTime): ?Task
    {
        // Find an unassigned task with matching difficulty and duration within the available work time
        return $unassignedTasks->where('difficulty', $experience)
            ->where('duration', '<=', $workTime)
            ->first();
    }

    private function logDebugInfoDuringTaskAssignment(?Task $task, int $experience, Schedule $schedule, float $workTime): void
    {
        // Log debug information during the task assignment process
        Log::debug('while loop', [
            'task_difficulty'              => $task?->difficulty,
            'developer_experience'         => $experience,
            'schedule_remaining_work_time' => $schedule->remaining_work_time,
            'work_time'                    => $workTime,
            'task_duration'                => $task?->duration,
        ]);
    }

    private function logTaskNotFoundForExperience(?Task $task, int $currentWeek): void
    {
         // Log that a task was not found for the specified experience difficulty
        Log::debug('Task does not found for this experience', [
            'week'           => $currentWeek,
            'new_task_difficulty' => $task?->difficulty,
        ]);
    }

    private function getLongestTaskByExperience($experience): ?Task
    {
        // Get the longest unassigned task based on difficulty
        $totalDurationsByDifficulty = Task::whereNull('developer_id')
            ->where('difficulty', '<', $experience)
            ->select('difficulty', DB::raw('sum(duration) as total'))
            ->orderByDesc('total')
            ->groupBy('difficulty')
            ->get();

        return $totalDurationsByDifficulty->sortByDesc('total')->first();
    }

    private function assignCurrentWeekTasks(Collection $tasks, Collection $schedules): void
    {
        // Assign tasks for the current week to schedules based on remaining work time
        $tasks->each(function (Task $task) use ($schedules): void {
            $data = $schedules->map(function (Schedule $schedule) use ($task): array {
                return [
                    'remaining_work_time' => $schedule->remaining_work_time - $schedule->developer->taskDuration($task),
                    'schedule'            => $schedule,
                ];
            });

            $schedule = $data->sortByDesc('remaining_work_time')->first()['schedule'];

            $schedule->updateTaskAndSchedule($task);
        });
    }

    private function updateScheduleAndTaskAssignment(Schedule $schedule, Task $task, Collection $unassignedTasks): void
    {
        // Update schedule and task assignment, and log debug information
        $schedule->updateTaskAndSchedule($task);

        $key = $unassignedTasks->search(fn (Task $item) => $item->id == $task->id);

        $unassignedTasks->forget($key);

        Log::debug('Task found for difficulty', [
            'developer_for_task' => $task->developer,
            'schedule_work_time' => $schedule->remaining_work_time,
            'schedule_tasks'     => $schedule->tasks,
        ]);
    }
}
