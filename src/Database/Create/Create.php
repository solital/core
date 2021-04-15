<?php

namespace Solital\Core\Database\Create;

use Solital\Core\Console\Style\Colors;
use Solital\Core\Console\Command\DatabaseCommand;
use Solital\Core\Database\ORM;
use Solital\Database\Create\SQL;

class Create
{
    /**
     * @var instance
     */
    protected $orm;

    /**
     * @var instance
     */
    private $colors;

    /**
     * @var string
     */
    private string $table;

    /**
     * @var string
     */
    private string $primary_key;

    /**
     * @var array
     */
    private array $columns;

    /**
     * Data when creating a standard user
     */
    public function __construct()
    {
        $this->colors = new Colors();
        $this->table = "tb_auth";
        $this->primary_key = "id_user";
        $this->columns = [
            "username",
            "password"
        ];

        $this->orm = new ORM($this->table, $this->primary_key, $this->columns);

        (new DatabaseCommand())->checkConnection();
    }

    /**
     * Creates a standard user in the database
     * 
     * @return void
     */
    public function userAuth(): void
    {
        $res = $this->orm
            ->createTable("tb_auth")
            ->int("id_user")->primary()->increment()
            ->varchar("username", 50)->notNull()
            ->varchar("password", 150)->notNull()
            ->closeTable()
            ->build();

        if ($res == true) {
            $msg = $this->colors->stringColor("Table created successfully!", "green", null, true);
            print_r($msg);

            $users = $this->orm->select()->build('ALL');

            if (empty($users)) {
                $this->orm->insert(['solital@email.com', '$2y$10$AeVWwNrzVXvGH6HqjEjZrOI31E4bzo8bxY6UhPc74qepsAG55StHu']);

                $msg = $this->colors->stringColor("User created successfully!", "green", null, true);
                print_r($msg);
            }
        } else {
            $msg = $this->colors->stringColor("Error: table not created!", "yellow", "red", true);
            print_r($msg);
        }
    }
}
