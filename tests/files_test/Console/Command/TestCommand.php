<?php 

namespace Solital\Console\Command;

use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;

/**
 * @generated class generated using Vinci Console
 */
class TestCommand extends Command implements CommandInterface
{
	/**
	 * @var string
	 */
	protected string $command = '';

	/**
	 * @var array
	 */
	protected array $arguments = [];

	/**
	 * @var array
	 */
	protected array $options = [];

	/**
	 * @var string
	 */
	protected string $description = '';


	/**
	 * @param object $arguments
	 * @param object $options
	 * @return mixed
	 */
	#[\Override]
	public function handle(object $arguments, object $options): mixed
	{
		echo 'Hello World';
		return $this;
	}
}
