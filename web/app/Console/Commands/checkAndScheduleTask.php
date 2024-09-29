<?php

namespace App\Console\Commands;
use App\Models\Session;
use App\Models\Task;
use App\Module\Shopify\Services\Task\TaskScheduleService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class checkAndScheduleTask extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'command:check-and-schedule-task';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Checks the task in the database';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle(TaskScheduleService $taskScheduleService)
	{
		// $shops = Session::whereNotNull('access_token')->get();
		$currentUtcTimestamp = Carbon::now('UTC')->startOfMinute()->timestamp;
		$tasks = Task::
			// where('id',1)
			// ->where('schedule_time', $currentUtcTimestamp)
			// ->get
			limit(1)->get();

		foreach ($tasks as $task) {
			$taskScheduleService->setJob($task);
			// $now = Carbon::parse(now())->toISOString();
			$task->update([
				'status' => 'processing'
			]);
		}
	}
}
