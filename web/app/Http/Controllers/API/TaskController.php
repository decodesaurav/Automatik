<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskAdjustment;
use App\Models\TaskCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Save Task First
            $taskData = [
                'task_type' => $request->task_type,
                'schedule_time' => $request->schedule_time,
                'revert_time' => $request->revert_time ?: null,
                'revert_time' => $request->frequency ?: null,
                'status' => $request->status ?? 'pending'
            ];
            $task = Task::create($taskData);
            
            // Save Task Conditions
            foreach ($request->conditions as $condition) {
                $conditionData = [
                    'task_id' => $task->id,
                    'condition_field' => $condition['field'],
                    'operator' => $condition['method'],
                    'value' => $condition['value'],
                ];
                TaskCondition::create($conditionData);
            }
            
            // Save Task Adjustment
            $adjustment = $request->adjustment;
            $adjustmentData = [
                'task_id' => $task->id,
                'adjustment_type' => $adjustment['adjustment_type'],
                'adjustment_method' => $adjustment['method'],
                'value' => $adjustment['value'],
                'adjustment_field' => $request->task_type, // Assuming adjustment_field is based on task_type
            ];
            TaskAdjustment::create($adjustmentData);
            DB::commit();
            return response()->json(['success' => true, 'task' => $task], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Task creation failed', 'error' => $e->getMessage()], 500);
        }
    }
}
