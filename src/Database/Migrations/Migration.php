<?php

namespace Solital\Core\Database\Migrations;

use Katrina\Katrina;
use Katrina\Sql\KatrinaStatement;
use Solital\Core\Kernel\Application;
use Solital\Core\FileSystem\HandleFiles;

use Solital\Core\Database\Migrations\{
    HandleMigrationTrait,
    Provider\MigratorVersionProviderDB,
    Exception\MigrationException
};

class Migration
{
    use HandleMigrationTrait;

    /**
     * @var string The path to the directory where migrations are stored.
     */
    protected string $migrations_directory;

    /**
     * @var array An array of all migrations installed for this app.
     */
    protected array $migration_files = [];

    /**
     * @var MigratorVersionProviderDB
     */
    protected MigratorVersionProviderDB $provider;

    /**
     * @var HandleFiles
     */
    protected HandleFiles $handle;

    /**
     * Create a migrator instance.
     */
    public function __construct()
    {
        $this->migrations_directory = Application::getRootApp('Database/migrations/', Application::DEBUG);
        $this->handle = new HandleFiles();
        $this->provider = new MigratorVersionProviderDB();
        $this->provider->initDB();

        // set up initial data
        $this->setMigrationsDirectory($this->migrations_directory);

        // say hello
        $this->initializeMigrationsDir();
        $this->collectMigrationFiles();
    }

    /**
     * @return void
     */
    protected function initializeMigrationsDir(): void
    {
        // initialize migrations dir
        $migrationsDir = $this->getMigrationsDirectory();
        if (!file_exists($migrationsDir)) {
            $handle = new HandleFiles();
            $handle->create($migrationsDir);
        }
    }

    /**
     * @return void
     */
    protected function collectMigrationFiles(): void
    {
        foreach (new \DirectoryIterator($this->getMigrationsDirectory()) as $file) {
            if ($file->isDot()) continue;
            if ($file->isDir()) continue;

            $file_name = str_replace(".php", "", $file->getFilename());
            $this->migration_files[$file_name] = $file->getFilename();

            // sort in reverse chronological order
            natsort($this->migration_files);
        }
    }

    /**
     * Get the migration name of the latest version.
     *
     * @return string The "latest" migration version.
     */
    /**
     * @return null|string
     */
    public function latestVersion(): ?string
    {
        if (empty($this->migration_files)) {
            $this->line("No migrations available")->print()->break();
            return true;
        }

        $migration_files = array_keys($this->migration_files);

        /**
         * Remove "version.txt" file
         */
        array_pop($migration_files);

        $lastMigration = array_pop($migration_files);

        return $lastMigration;
    }

    /**
     * Create a migrate stub file.
     *
     * Creates a new migration file in the migrations directory with a basic template for writing a migration.
     * 
     * @param string $migration_name
     * 
     * @return void
     */
    public function createMigration(?string $migration_name): void
    {
        $dts = date('Ymd_His');

        if ($migration_name == null) {
            $migration_name = "";
        } else {
            $migration_name = "_" . $migration_name;
        }

        $migration_explode = explode("_", $migration_name);

        $file_name = $dts . $migration_name . '.php';
        $file_name = strtolower($file_name);
        $file_path = $this->getMigrationsDirectory() . DIRECTORY_SEPARATOR . $file_name;

        $this->generateMigrationClass($migration_explode, $file_path, $file_name, $dts);

        $file_name = $this->warning($file_name)->getMessage();
        $file_path = $this->success($file_path)->getMessage();
        $msg1 = $this->line("\nCreated migration ")->getMessage();
        $msg2 = $this->line(" at ")->getMessage();

        echo $msg1 . $file_name . $msg2 . $file_path . PHP_EOL;
    }

    /**
     * @param array $migration_explode
     * @param string $file_path
     * @param string $file_name
     * @param string $dts
     * 
     * @return void
     */
    private function generateMigrationClass(
        array $migration_explode,
        string $file_path,
        string $file_name,
        string $dts
    ): void {

        if (in_array("create", $migration_explode)) {
            $migration_file_template = "TableMigrationTemplate";
        } else {
            $migration_file_template = "MigrationTemplate";
        }

        $template = Application::getConsoleComponent("Migrations/{$migration_file_template}.php");

        if (file_exists($file_path)) {
            $this->error("Migration '{$file_name}' already exists. Aborting!")->print()->break()->exit();
        }

        $output_template = file_get_contents($template);

        if (str_contains($output_template, "NameDefault")) {
            $migration_name = str_replace("_", "", $dts);
            $output_template = str_replace("NameDefault", $migration_name, $output_template);

            if (str_contains($output_template, "TableName")) {
                $output_template = str_replace("TableName", $migration_explode[2], $output_template);
            }
        }

        file_put_contents($file_path, $output_template);
    }

    /**
     * @param object $options
     * 
     * @return void
     */
    public function runMigrationsDB(object $options): void
    {
        $migrations_db = Katrina::customQuery("SELECT * FROM migrations", true);
        $migrations_standby = $this->compareFileWithDB($migrations_db, $options);

        if (empty($migrations_db)) {
            $this->runMigrationsFiles($options, $migrations_standby);
        } else {
            /**
             * AQUI SE AS MIGRATIONS FORAM EXECUTADAS E ESTÃO NO BANCO DE DADOS
             */
            foreach ($migrations_standby as $migrate_name => $migrates) {
                $instance = $this->instantiateMigration($migrate_name);

                if ($instance != null) {
                    if (isset($options->rollback)) {
                        $this->provider->delete("name = '" . $migrate_name . "'");

                        $instance->down();
                    } else {
                        KatrinaStatement::executePrepare("UPDATE migrations SET name = '{$migrates->name}'");

                        $instance->up();
                    }
                }
            }
        }
    }

    /**
     * @param mixed $migrations_db
     * @param object $options
     * 
     * @return void
     */
    private function runRollback(mixed $migrations_db, object $options): void
    {
        $start_time = microtime(true);

        if (!is_bool($options->rollback)) {
            $migrations_db = $this->convertMigrationsObject($migrations_db, $options);
        }

        foreach ($migrations_db as $migrations_db) {
            $this->warning("Rollback migration: " . $migrations_db->name)->print()->break();

            $instance = $this->instantiateMigration($migrations_db->name);
            $this->provider->delete("name = '" . $migrations_db->name . "'");

            $instance->down();

            $this->success("Rollback executed: " . $migrations_db->name)->print()->break();
        }

        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time);

        echo PHP_EOL;
        $this->success("Migrations performed on: " . $execution_time . " sec")->print()->break()->exit();
    }

    /**
     * @param object $options
     * 
     * @return void
     */
    public function runMigrationsFiles(object $options, array $files_standby): void
    {
        $all_files = [];
        $start_time = microtime(true);

        if (isset($files_standby)) {
            foreach ($files_standby as $files) {
                $all_files[] = $this->getMigrationsDirectory() . $files;
            }
        } else {
            $all_files = $this->handle->folder($this->getMigrationsDirectory())->files();
        }

        foreach ($all_files as $migration_file) {
            $this->warning("Running migration: " . $migration_file)->print()->break();

            $migration_file = str_replace(".php", "", $migration_file);
            $instance = $this->instantiateMigration(basename($migration_file));

            if (isset($options->rollback)) {
                $this->provider->delete("name = '" . basename($migration_file) . "'");

                $instance->down();
            } else {
                try {
                    $migrator = new MigratorVersionProviderDB();
                    $migrator->name = basename($migration_file);
                    $migrator->save();

                    $instance->up();

                    $this->success("Migration executed: " . $migration_file)->print()->break();
                } catch (MigrationException $e) {
                    $this->error($e->getMessage() . ": " . $migration_file)->print()->break();
                }
            }
        }

        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time);

        echo PHP_EOL;
        $this->success("Migrations performed on: " . $execution_time . " sec")->print()->break()->exit();
    }

    /**
     * @param string $migrationName
     * 
     * @return mixed
     */
    private function instantiateMigration(string $migrationName): mixed
    {
        if (isset($this->migration_files[$migrationName])) {
            $migration_file = $this->getMigrationsDirectory() . $this->migration_files[$migrationName];
        } else {
            return null;
        }

        if (file_exists($migration_file)) {
            require_once($migration_file);

            $migrationName = explode("_", $migrationName);
            $migrationName = $migrationName[0] . $migrationName[1];

            $migrationClassName = "Migration{$migrationName}";

            return new $migrationClassName();
        }

        return null;
    }
}