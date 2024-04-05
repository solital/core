<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\{Interface\CommandInterface, Command};
use Solital\Core\Console\Output\ConsoleOutput;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Kernel\Dotenv;
use Solital\Core\Security\Hash;

class GenerateHash extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "generate:hash";

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var string
     */
    protected string $description = "Generate a random hash";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        if (!Dotenv::isset('APP_HASH')) {
            $hash = Hash::randomString();
            Dotenv::add('APP_HASH', $hash);

            ConsoleOutput::success("APP_HASH successfully generated!")->print();
            return true;
        }

        if (getenv('APP_HASH') == '') {
            $hash = Hash::randomString();
            Dotenv::edit('APP_HASH', $hash);

            ConsoleOutput::success("APP_HASH successfully generated!")->print();
            return true;
        }
        
        ConsoleOutput::error("APP_HASH already exists!")->print();
        return true;
    }
}
