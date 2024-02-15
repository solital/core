<?php 

namespace Solital\Console\Command;

use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;

/**
 * @generated class generated using Vinci Console
 */
class UserCommand extends Command implements CommandInterface
{
	/**
	 * @var string
	 */
	protected string $command = 'mycmd';

	/**
	 * @var array
	 */
	protected array $arguments = [];

	/**
	 * @var string
	 */
	protected string $description = 'test';


	/**
	 * @param object $arguments
	 * @param object $options
	 * @return mixed
	 */
	public function handle(object $arguments, object $options): mixed
	{
		echo 'Hello World!';
		return $this;
	}
}
