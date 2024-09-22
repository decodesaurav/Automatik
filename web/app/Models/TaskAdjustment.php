<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAdjustment extends Model
{
    protected $fillable = [
        'task_id',
        'adjustment_type',
        'adjustment_field',
        'adjustment_method',
        'value',
    ];

    // Relationship to the parent task
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
