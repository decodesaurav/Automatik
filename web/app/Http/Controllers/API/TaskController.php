<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\Task;
use App\Models\TaskAdjustment;
use App\Models\TaskCondition;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $session = $request->get('shopifySession');
        $session = Session::where('session_id', $session->getId())->first();

        $tasks = Task::where(['session_id' => $session->id])->paginate(10);
        return response()->json(['success' => true, 'data' => $tasks]);
    }

	public function store(Request $request)
	{
		DB::beginTransaction();
		try {
			$session = $request->get('shopifySession');
			$session = Session::where('session_id', $session->getId())->first();
			$set_unix_timestamp = $this->setUnixTimestamp($request->schedule_time);
			// Save Task First
			$taskData = [
				'session_id' => $session->id,
                'task_name' => $request->task_name,
				'task_type' => $request->task_type,
				'schedule_time' => $set_unix_timestamp,
				'revert_time' => $request->revert_time ?: null,
				'frequency' => $request->frequency ?: null,
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

	private function setUnixTimestamp($timeStamp)
	{
		$localDateTime = $timeStamp;
		$userTimeZone = 'America/New_York'; //change this later and get from the settings
		$localizedDateTime = Carbon::createFromFormat('Y-m-d H:i', $localDateTime, $userTimeZone);
		$localizedDateTime->setSecond(0);
		$utcDateTime = $localizedDateTime->setTimezone('UTC');
		$unixTimeStamp = $utcDateTime->timestamp;
		return $unixTimeStamp;
	}
}
