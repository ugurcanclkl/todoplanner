<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Developer extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType   = 'string';
    protected $guarded   = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function taskDuration(Task $task): float|int
    {
        return ($task->difficulty * $task->duration) / $this->experience;
    }
    
    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $developer): void {
            $developer->id = (string) Str::uuid();
        });
    }
}
