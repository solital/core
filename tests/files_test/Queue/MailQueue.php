<?php 

/**
 * @generated class generated using Vinci Console
 */
class MailQueue
{
	/**
	 * Send queue email
	 */
	public function dispatch()
	{
		(new Solital\Core\Mail\Mailer)->sendQueue();
	}
}
