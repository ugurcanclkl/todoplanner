<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Task extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType   = 'string';
    protected $guarded   = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $task): void {
            $task->id = (string) Str::uuid();
        });
    }
}
