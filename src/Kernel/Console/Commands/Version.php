<?php

namespace Solital\Core\Kernel\Console\Commands;

use Katrina\Katrina;
use Solital\Core\Console\Command;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Kernel\Application;

class Version extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "version";

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var string
     */
    protected string $description = "Check the Solital Framework version";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    public function handle(object $arguments, object $options): mixed
    {
        $this->info("Solital framework - Fast, easy and practical")->print()->break(true);
        $this->line("Core " . $this->getVersion())->print()->break();
        $this->line("PHP " . $this->phpVersion())->print()->break();
        $this->line("Katrina " . $this->katrinaVersion())->print()->break(true);
        $this->warning("Thank you for using Solital, you can see the full documentation at http://solitalframework.com/")->print()->break();

        return true;
    }

    /**
     * @return string
     */
    public function solitalVersion(): string
    {
        return Application::SOLITAL_VERSION;
    }

    /**
     * @return mixed
     */
    public function katrinaVersion(): mixed
    {
        return Katrina::KATRINA_VERSION;
    }

    /**
     * @return string
     */
    public function phpVersion(): string
    {
        return PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION . "." . PHP_RELEASE_VERSION;
    }
}
