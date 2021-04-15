<?php

namespace Solital\Core\Database;

use Solital\Core\Console\Version;
use Solital\Core\Console\Style\Table;
use Solital\Core\Database\Create\Create;
use Solital\Core\Console\Command\CustomCommand;
use Solital\Core\Console\Command\DatabaseCommand;
use Solital\Core\Console\Command\CustomConsoleInterface;

class ORMConsole extends CustomCommand implements CustomConsoleInterface
{
    public function __construct()
    {
        $this->table = new Table();
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        return [
            'katrina-version' => 'katrinaVersion',
            'katrina-auth' => 'katrinaAuth',
            'katrina-dump' => 'dumpDb'
        ];
    }

    
    public function katrinaVersion(): ORMConsole
    {
        $version = [
            [
                'version' => Version::katrinaVersion()
            ]
        ];

        $this->table->setTableColor('cyan');
        $this->table->setHeaderColor('cyan');
        $this->table->addField('KATRINA ORM VERSION', 'version', false, 'white');
        $this->table->injectData($version);
        $this->table->display();

        return $this;
    }

    /**
     * @return ORMConsole
     */
    public function katrinaAuth(): ORMConsole
    {
        (new Create())->userAuth();

        return $this;
    }

    /**
     * @return ORMConsole
     */
    public function dumpDb(): ORMConsole
    {
        (new DatabaseCommand())->dump();

        return $this;
    }
}
