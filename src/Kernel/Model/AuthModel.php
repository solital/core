<?php

namespace Solital\Core\Kernel\Model;

use Katrina\Katrina;
use Solital\Core\Console\Output\ConsoleOutput;

class AuthModel extends Katrina
{
    /**
     * @var string|null
     */
    protected ?string $table = "auth_users";

    /**
     * @var bool
     */
    protected bool $timestamp = false;

    /**
     * @return mixed
     */
    public function createUserTable(): mixed
    {
        $users = self::checkTableExists('auth_users');

        if (empty($users)) {
            if (DB_CONFIG['DRIVE'] != "") {
                if (DB_CONFIG['DRIVE'] == "mysql") {
                    $res = self::createTable("auth_users")
                        ->int('id')->primary()->increment()
                        ->varchar("username", 50)->notNull()
                        ->varchar("password", 150)->notNull()
                        ->createdUpdatedAt()
                        ->closeTable();
                } elseif (DB_CONFIG['DRIVE'] == "pgsql") {
                    $res = self::createTable("auth_users")
                        ->serial('id')->primary()
                        ->varchar("username", 50)->notNull()
                        ->varchar("password", 150)->notNull()
                        ->closeTable();
                }

                if ($res == true) {
                    ConsoleOutput::success("Table created successfully!")->print()->break();

                    $users = self::customQuery("SELECT * FROM auth_users", true);
                    return $users;
                }

                ConsoleOutput::error("Error: table not created!")->print()->break();
                return false;
            }
        }

        return $users;
    }
}
