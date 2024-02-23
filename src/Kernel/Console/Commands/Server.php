<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Kernel\Application;
use Solital\Core\Process\{Process, ProcessException};

class Server extends Command implements CommandInterface
{
	/**
	 * @var string
	 */
	protected string $command = "server";

	/**
	 * @var array
	 */
	protected array $arguments = [];

	/**
	 * @var string
	 */
	protected string $description = "Starts built-in PHP server";

	/**
	 * @var string
	 */
	private string $host = "127.0.0.1:8080";

	/**
	 * @param object $arguments
	 * @param object $options
	 * @return mixed
	 */
	#[\Override]
	public function handle(object $arguments, object $options): mixed
	{
		if (Application::DEBUG == true) {
            $this->error("Debug mode enabled! Server won't working!")->print()->break();
			return false;
        }

		if (isset($options->host)) {
			$this->host = $options->host;
		}

		$this->success("Server started at " . date("H:i:s"))->print()->break();
		$this->success("Host: " . $this->host)->print()->break();
		$this->info("Press Ctrl+C to cancel host")->print()->break();

		$process = Process::executeCommand("php -S " . $this->host . " -t public/");

		if ($process->getOutput() !== null) {
			throw new ProcessException($process->getOutput());
		}

		return $this;
	}
}
