<?php 

/**
 * @generated class generated using Vinci Console
 */
class TestQueue extends Solital\Core\Queue\Queue
{
	/**
	 * dispatch
	 */
	public function dispatch()
	{
		echo 'queue 1' . PHP_EOL;
	}
}
