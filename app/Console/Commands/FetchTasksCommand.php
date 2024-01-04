<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ApiProvider1Service;
use App\Services\ApiProvider2Service;
use App\Events\AssignTasksEvent;
use App\Services\TaskServiceContext;
use App\Models\Schedule;

class FetchTasksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-tasks-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fetches tasks from providers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // usage with API Provider 1
        $apiProvider1 = new ApiProvider1Service();
        $context1 = new TaskServiceContext($apiProvider1);
                
        // usage with API Provider 2
        $apiProvider2 = new ApiProvider2Service();
        $context2 = new TaskServiceContext($apiProvider2);

        // get currentWeek to assign Tasks
        $currentWeek = Schedule::orderBy('week', 'desc')->first()?->week ?? 1;
        
        // Dispatch AssignTasksEvent
        event(new AssignTasksEvent($currentWeek));

        return Command::SUCCESS;
    }
}
