<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCondition extends Model
{
    protected $fillable = [
        'task_id',
        'condition_field',
        'operator',
        'value',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
