<?php 

namespace Solital\Schedule;

use Solital\Core\Schedule\Attribute\EveryMinute;
use Solital\Core\Schedule\ScheduleInterface;
use Solital\Core\Schedule\TaskSchedule;

/**
 * @generated class generated using Vinci Console
 */
#[EveryMinute]
class InvoiceSchedule extends TaskSchedule implements ScheduleInterface
{
	/**
	 * Construct with schedule time
	 */
	public function __construct()
	{
		$this->time = "everyMinute";
	}


	/**
	 * @return mixed
	 */
	public function handle(): mixed
	{
		return $this;
	}
}
