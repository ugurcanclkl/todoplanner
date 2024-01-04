<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssignTasksEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The total weekly hours available.
     *
     * @var int
     */
    public $currentWeek;

    /**
     * Create a new event instance.
     */
    public function __construct($currentWeek)
    {
        $this->currentWeek = $currentWeek;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
