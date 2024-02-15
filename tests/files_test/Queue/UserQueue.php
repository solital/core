<?php 

namespace Solital\Queue;

use Solital\Core\Queue\Queue;

/**
 * @generated class generated using Vinci Console
 */
class UserQueue extends Queue
{
	protected float $sleep = 1;
	
	/**
	 * dispatch
	 */
	public function dispatch()
	{
		echo 'queue 2' . PHP_EOL;
	}
}
