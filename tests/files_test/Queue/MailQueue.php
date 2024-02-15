<?php

namespace Solital\Queue;

use Solital\Core\Queue\Queue;

/**
 * @generated class generated using Vinci Console
 */
class MailQueue extends Queue
{
	protected float $sleep = 1;
	
	/**
	 * Send queue email
	 */
	public function dispatch()
	{
		//(new Solital\Core\Mail\Mailer)->sendQueue();
	}
}
