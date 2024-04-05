<?php

namespace Solital\Core\Kernel\Model;

use Katrina\Katrina;

class MigrationModel extends Katrina
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
            $res = self::createTable("migrations");
            
            if (DB_CONFIG['DRIVE'] == "mysql") {
                $res->int('id')->primary()->increment();
            }

            if (DB_CONFIG['DRIVE'] == "pgsql") {
                $res->serial('id')->primary();
            }

            $res->varchar("name", 100)->notNull();
            $res->createdUpdatedAt();
            $res->closeTable();

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
