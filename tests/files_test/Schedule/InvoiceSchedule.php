<?php 

namespace Solital\Schedule;

use Solital\Core\Schedule\Schedule;
use Solital\Core\Schedule\ScheduleInterface;

/**
 * @generated class generated using Vinci Console
 */
class InvoiceSchedule extends Schedule implements ScheduleInterface
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
		return true;
	}
}
