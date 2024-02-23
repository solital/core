<?php

namespace Solital\Core\Kernel\Console\Commands;

use Katrina\Katrina;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Kernel\Application;
use Solital\Core\Console\{Command, Table, TableBuilder};

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
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        $this->info("Solital framework - Fast, easy and practical")->print()->break(true);

        Table::formattedRowData([
            'Solital Core' => $this->solitalVersion(),
            'PHP Version' => $this->phpVersion(),
            'Katrina ORM' => $this->katrinaVersion()
        ], 20);

        echo PHP_EOL;
        $this->warning("Thank you for using Solital, you can see the full documentation at " . Application::SITE_DOC_DOMAIN)->print()->break();

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
