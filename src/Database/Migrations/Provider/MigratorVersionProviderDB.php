<?php

namespace Solital\Core\Database\Migrations\Provider;

use Katrina\Katrina;

class MigratorVersionProviderDB extends Katrina
{
    /**
     * @var string|null
     */
    protected ?string $table = "migrations";

    /**
     * @var bool
     */
    protected bool $timestamp = false;

    /**
     * @return mixed
     */
    public function initDB(): mixed
    {
        if (defined('DB_CONFIG')) {
            if (DB_CONFIG['DRIVE'] == "mysql") {
                $res = self::createTable("migrations")
                    ->int('id')->primary()->increment()
                    ->varchar("name", 100)->notNull()
                    ->createdUpdateAt()
                    ->closeTable();
            }
            
            if (DB_CONFIG['DRIVE'] == "pgsql") {
                $res = self::createTable("migrations")
                    ->serial('id')->primary()
                    ->varchar("name", 100)->notNull()
                    ->closeTable();
            }

            if (!isset($res)) {
                return $this;
            }

            return $res;
        }

        return $this;
    }

    /**
     * @return void
     */
    public function getVersion(): void
    {
        $this->initDB();
    }
}
