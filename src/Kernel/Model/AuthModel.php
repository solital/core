<?php

namespace Solital\Core\Kernel\Model;

use Katrina\Katrina;
use Katrina\Sql\KatrinaStatement;
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
    public static function createUserTable(): mixed
    {
        $users = self::checkTableExists('auth_users');

        if (empty($users)) {
            if (DB_CONFIG['DRIVE'] != "") {
                $user_table = self::createTable("auth_users");

                if (DB_CONFIG['DRIVE'] == "mysql") {
                    $user_table->int('id')->primary()->increment();
                }
                
                if (DB_CONFIG['DRIVE'] == "pgsql") {
                    $user_table->serial('id')->primary();
                }

                $user_table->varchar("username", 50)->notNull();
                $user_table->varchar("password", 150)->notNull();

                if (DB_CONFIG['DRIVE'] == "mysql") {
                    $user_table->createdUpdatedAt();
                }

                $user_table->closeTable();

                if ($user_table == true) {
                    ConsoleOutput::success("Table created successfully!")->print()->break();

                    $users = KatrinaStatement::executeQuery("SELECT * FROM auth_users", true);
                    return $users;
                }

                ConsoleOutput::error("Error: table not created!")->print()->break();
                return false;
            }
        }

        return $users;
    }
}
