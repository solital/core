<?php

namespace Solital\Core\Database\Migrations;

use Katrina\Katrina;
use Katrina\Sql\KatrinaStatement;
use Solital\Core\Kernel\Application;
use Solital\Core\FileSystem\HandleFiles;
use Solital\Core\Console\Output\ConsoleOutput;
use Solital\Core\Database\{Exceptions\MigrationException, Migrations\HandleMigrationTrait};
use Solital\Core\Kernel\{DebugCore, Model\MigrationModel};
use Nette\PhpGenerator\{ClassType, Method, PhpNamespace};

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
     * @var MigrationModel
     */
    protected MigrationModel $provider;

    /**
     * @var HandleFiles
     */
    protected HandleFiles $handle;

    /**
     * Migration shoud be ignored?
     * 
     * @var bool
     */
    protected bool $ignore_migration = false;

    /**
     * Create a migrator instance.
     */
    public function __construct()
    {
        $this->migrations_directory = Application::getRootApp('Database/migrations/', DebugCore::isCoreDebugEnabled());
        $this->handle = Application::provider('handler-file');
        $this->provider = new MigrationModel();
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
            $this->handle->create($migrationsDir);
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
            ConsoleOutput::line("No migrations available")->print()->break();
            return null;
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
            $migration_name = '_' . 'generate';
        } else {
            $migration_name = '_' . $migration_name;
        }

        $migration_explode = explode('_', $migration_name);

        $file_name = $dts . $migration_name . '.php';
        $file_name = strtolower($file_name);
        $file_path = $this->getMigrationsDirectory() . DIRECTORY_SEPARATOR . $file_name;

        ConsoleOutput::debugMessage('Create migration ', 'MIGRATION', 49)->print()->break(true);

        ConsoleOutput::status($file_name, function () use ($migration_explode, $file_path, $file_name) {
            $this->generateMigrationClass($migration_explode, $file_path, $file_name);
            return $this;
        })->printStatus();
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
        string $file_name
    ): void {

        $migration_explode = explode("_", $file_name);
        $migration_date = $migration_explode[0] . $migration_explode[1];

        if (in_array("create", $migration_explode)) {

            $table_name = "table_name";

            if (isset($migration_explode[3])) {
                $table_name = str_replace(".php", "", $migration_explode[3]);
            }

            $up_body = "Katrina::createTable('" . $table_name . "')
    ->int('id')->primary()->increment()
    // ...
    ->createdUpdatedAt()
    ->closeTable();";
            $down_body = "Katrina::dropTable('" . $table_name . "');";
        } else {
            $up_body = "// ...";
            $down_body = "// ...";
        }

        if (file_exists($file_path)) {
            ConsoleOutput::error("Migration '{$file_name}' already exists. Aborting!")->print()->break()->exit();
        }

        $up_method = (new Method('up'))
            ->setPublic()
            ->setBody($up_body)
            ->addComment("Run migration" . "\n\n@return mixed");

        $down_method = (new Method('down'))
            ->setPublic()
            ->setBody($down_body)
            ->addComment("Roolback migration" . "\n\n@return mixed");

        $class = (new ClassType('Migration' . $migration_date))
            ->setExtends(Migration::class)
            ->addMember($up_method)
            ->addMember($down_method)
            ->addComment("@generated class generated using Vinci Console");

        $data = (new PhpNamespace("Solital\Database\migrations"))
            ->add($class)
            ->addUse(Katrina::class)
            ->addUse(Migration::class);

        file_put_contents($file_path, "<?php \n\n" . $data);
    }

    /**
     * @param object $options
     * 
     * @return void
     */
    public function runMigrationsDB(object $options): void
    {
        $migrations_db = KatrinaStatement::executeQuery("SELECT * FROM migrations", true);
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
                        $this->provider->delete("name", $migrate_name);
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
     * @param object|int $options
     * 
     * @return void
     */
    private function runRollback(mixed $migrations_db, object|int $options): void
    {
        $start_time = microtime(true);

        if (isset($options->rollback) && !is_bool($options->rollback)) {
            $migrations_db = $this->convertMigrationsObject($migrations_db, $options);
        }

        foreach ($migrations_db as $migration_db) {
            ConsoleOutput::warning("Rollback migration: " . $migration_db->name)->print()->break();

            ConsoleOutput::status($migration_db->name, function () use ($migration_db) {
                try {
                    $instance = $this->instantiateMigration($migration_db->name);

                    if ($instance === null) {
                        return false;
                    }

                    $this->provider->delete("name", $migration_db->name);
                    $instance->down();

                    return true;
                } catch (MigrationException $e) {
                    return false;
                }
            })->printStatus();
        }

        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time);

        echo PHP_EOL;
        ConsoleOutput::success("Migrations performed on: " . $execution_time . " sec")->print()->break();
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
            ConsoleOutput::warning("Running migration: " . basename($migration_file))->print()->break();

            $migration_file = str_replace(".php", "", $migration_file);

            ConsoleOutput::status(basename($migration_file), function () use ($migration_file, $options) {
                $instance = $this->instantiateMigration(basename($migration_file));

                if ($instance === null) return false;
                if ($instance->isIgnored() == true) return false;

                if (isset($options->rollback)) {
                    $this->provider->delete("name", basename($migration_file));
                    $instance->down();
                } else {
                    try {
                        $migrator = new MigrationModel();
                        $migrator->name = basename($migration_file);
                        $migrator->save();
                        $instance->up();
                        return true;
                    } catch (MigrationException $e) {
                        return false;
                    }
                }
            })->printStatus();
        }

        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time);

        echo PHP_EOL;
        ConsoleOutput::success("Migrations performed on: " . $execution_time . " sec")->print()->break()->exit();
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
            include_once $migration_file;

            $migrationName = explode("_", $migrationName);
            $migrationName = $migrationName[0] . $migrationName[1];

            $migrationClassName = "Migration{$migrationName}";

            $class = "Solital\Database\migrations\\" . $migrationClassName;

            if (class_exists($class)) {
                $instance = new \ReflectionClass($class);
                return $instance->newInstance();
            };
        }

        return null;
    }

    /**
     * Migration shoud be ignored?
     *
     * @return bool
     */
    private function isIgnored(): bool
    {
        return $this->ignore_migration;
    }
}
