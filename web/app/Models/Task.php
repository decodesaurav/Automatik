<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'task_type',
        'schedule_time',
        'revert_time',
        'frequency',
        'status',
        'session_id'
    ];

    // Define relationships
    public function conditions()
    {
        return $this->hasMany(TaskCondition::class);
    }

    public function adjustments()
    {
        return $this->hasMany(TaskAdjustment::class);
    }

}
