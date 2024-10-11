<?php

namespace Solital\Core\Session;

use Katrina\Katrina;

class SessionMigration
{
    /**
     * Run migration
     * 
     * @return mixed
     */
    public function up(): mixed
    {
        return Katrina::createTable("session")
            ->varchar("id", 256)->notNull()
            ->varchar("name", 256)->notNull()
            ->longtext("value", 256)
            ->int("last_update", 20)->notNull()
            ->closeTable();
    }

    /**
     * Roolback migration
     * 
     * @return mixed
     */
    public function down(): mixed
    {
        return Katrina::dropTable("session");
    }
}
