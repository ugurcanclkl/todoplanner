<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use DB;

class Schedule extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType   = 'string';
    protected $guarded   = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function updateTaskAndSchedule(Task $task): void
    {
        DB::transaction(function () use ($task): void {
            $task->update([
                'developer_id' => $this->developer->id,
                'schedule_id' => $this->id,
            ]);

            $this->update([
                'remaining_work_time' => $this->remaining_work_time - $this->developer->taskDuration($task),
            ]);
        });
    }
        
    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $schedule): void {
            $schedule->id = (string) Str::uuid();
        });
    }
}
