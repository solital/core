<?php

namespace Solital\Core\Database\Migrations;

use Solital\Core\Console\Output\ConsoleOutput;

trait HandleMigrationTrait
{
    /**
     * @param string $migrations_directory
     * 
     * @return self
     */
    public function setMigrationsDirectory(string $migrations_directory): self
    {
        $this->migrations_directory = $migrations_directory;

        return $this;
    }

    /**
     * @return string
     */
    public function getMigrationsDirectory(): string
    {
        return $this->migrations_directory;
    }

    /**
     * @param mixed $migrations_db
     * @param object $options
     * 
     * @return mixed
     */
    private function compareFileWithDB(mixed $migrations_db, object $options): mixed
    {
        $to_array = [];

        if (isset($options->rollback)) {
            $this->runRollback($migrations_db, $options);
        }

        foreach ($migrations_db as $migrations_db) {
            $migrations_db_array = (array)$migrations_db;
            $to_array[] = $migrations_db_array['name'] . ".php";
        }

        $migrations_standby = array_diff($this->migration_files, $to_array);

        if (isset($options->status)) {
            if (empty($migrations_standby)) {
                ConsoleOutput::success("All migrations have been executed!")->print()->break();
            } else {
                ConsoleOutput::line("The migrations below were not executed:")->print()->break(true);

                foreach ($migrations_standby as $migrations) {
                    ConsoleOutput::warning($migrations)->print()->break();
                }
            }

            exit;
        }

        if (empty($migrations_standby)) {
            ConsoleOutput::success("All migrations have been executed!")->print()->break()->exit();
        }

        if (isset($migrations_standby)) {
            $this->runMigrationsFiles($options, $migrations_standby);
        }

        return $migrations_standby;
    }

    /**
     * @param mixed $migrations
     * @param object $options
     * 
     * @return mixed
     */
    private function convertMigrationsObject(mixed $migrations, object $options): mixed
    {
        foreach ($migrations as $migrations) {
            $migrations_array[] = (array)$migrations;
        }

        $reverse = array_reverse($migrations_array);
        $res = array_slice($reverse, 0, (int)$options->rollback);

        foreach ($res as $value) {
            $values[] = (object)$value;
        }

        return $values;
    }
}
