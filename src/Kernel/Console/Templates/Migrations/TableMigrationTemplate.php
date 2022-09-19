<?php

use Katrina\Katrina;
use Solital\Core\Database\Migrations\Migration;

class MigrationNameDefault extends Migration
{
    /**
     * Run migration
     * 
     * @return mixed
     */
    public function up()
    {
        Katrina::createTable("TableName")
            ->int('id')->primary()->increment()
            // ...
            ->createdUpdateAt()
            ->closeTable();
    }

    /**
     * Roolback migration
     * 
     * @return mixed
     */
    public function down()
    {
        Katrina::dropTable("TableName");
    }
}
