<?php

namespace Solital\Core\Database\Dump;

use Solital\Core\Database\Dump\Exception\DumpException;
use Solital\Core\Kernel\Application;
use Spatie\DbDumper\Databases\{MySql, PostgreSql, Sqlite};

class Dump
{
    /**
     * @var mixed
     */
    private mixed $dump = null;

    /**
     * @var array
     */
    private array $yaml_data;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->yaml_data = Application::yamlParse('database.yaml');

        $this->checkDbConnection();
        $this->getDatabaseDrive();
        $this->getWindowsDumpBinary();

        if (Application::DEBUG == false) {
            if (defined('DB_CONFIG')) {
                $db_name = getenv('DB_NAME');
                $db_user = getenv('DB_USER');
                $db_pass = getenv('DB_PASS');
            }
        } else {
            if (defined('DB_CONFIG')) {
                $db_name = DB_CONFIG['DBNAME'];
                $db_user = DB_CONFIG['USER'];
                $db_pass = DB_CONFIG['PASS'];
            }
        }

        $this->dump->setDbName($db_name);
        $this->dump->setUserName($db_user);
        $this->dump->setPassword($db_pass);
    }

    /**
     * @return Dump
     * @throws DumpException
     */
    private function checkDbConnection(): Dump
    {
        if (DB_CONFIG['DBNAME'] == "" || DB_CONFIG['USER'] == "") {
            throw new DumpException("Database constants not configured");
        }

        return $this;
    }

    /**
     * @return Dump
     * @throws DumpException
     */
    private function getDatabaseDrive(): Dump
    {
        if (defined("DB_CONFIG")) {
            $this->dump = match (DB_CONFIG['DRIVE']) {
                'mysql' => MySql::create(),
                'pgsql' => PostgreSql::create(),
                'sqlite' => Sqlite::create(),
                default => throw new DumpException("Database drive not valid")
            };

            return $this;
        }

        throw new DumpException("'DB_CONFIG' constante not defined");
    }

    /**
     * @return Dump
     */
    private function getWindowsDumpBinary(): Dump
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            switch (DB_CONFIG['DRIVE']) {
                case 'mysql':
                    if (!is_dir($this->yaml_data['dump_windows']['mysql'])) {
                        throw new \InvalidArgumentException('Directory "' . $this->yaml_data['dump_windows']['mysql'] . '" in "database.yaml" on variable "dump_windows" not found');
                    }

                    $this->dump->setDumpBinaryPath($this->yaml_data['dump_windows']['mysql']);
                    break;

                case 'pgsql':
                    if (!is_dir($this->yaml_data['dump_windows']['pgsql'])) {
                        throw new \InvalidArgumentException('Directory "' . $this->yaml_data['dump_windows']['pgsql'] . '" in "database.yaml" on variable "dump_windows" not found');
                    }

                    $this->dump->setDumpBinaryPath($this->yaml_data['dump_windows']['pgsql']);
                    break;

                case 'sqlite':
                    if (!is_dir($this->yaml_data['dump_windows']['sqlite'])) {
                        throw new \InvalidArgumentException('Directory "' . $this->yaml_data['dump_windows']['sqlite'] . '" in "database.yaml" on variable "dump_windows" not found');
                    }

                    $this->dump->setDumpBinaryPath($this->yaml_data['dump_windows']['sqlite']);
                    break;
            }
        }

        return $this;
    }

    /**
     * @param string $tables
     * 
     * @return Dump
     */
    public function excludeTables(string $tables): Dump
    {
        $tables = explode(",", $tables);
        $this->dump->excludeTables($tables);

        return $this;
    }

    /**
     * @param string $dir_dump
     * 
     * @return Dump
     */
    public function dumpDatabase(string $dir_dump): Dump
    {
        $dump_file = "dump-" . date('Ymd-His') . ".sql";
        $this->dump->dumpToFile($dir_dump . DIRECTORY_SEPARATOR . $dump_file);

        return $this;
    }
}
