<?php 

namespace Solital\Schedule;

use Solital\Core\Schedule\Schedule;
use Solital\Core\Schedule\ScheduleInterface;

/**
 * @generated class generated using Vinci Console
 */
class MailSchedule extends Schedule implements ScheduleInterface
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
		echo 'MAILSCHEDULE TEST' . PHP_EOL;
		return $this;
	}
}
