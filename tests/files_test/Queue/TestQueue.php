<?php 

namespace Solital\Queue;

use Solital\Core\Queue\Queue;

/**
 * @generated class generated using Vinci Console
 */
class TestQueue extends Queue
{
	protected float $sleep = 1;
	
	/**
	 * dispatch
	 */
	public function dispatch()
	{
		echo 'queue 1' . PHP_EOL;
	}
}
