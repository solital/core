<?php

namespace Solital\Core\Database\Dump;

use Solital\Core\Database\Dump\Exception\DumpException;
use Symfony\Component\Yaml\Yaml;
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
        $this->yaml_data = Yaml::parseFile(Application::getDirConfigFiles(5) . '/database.yaml');

        $this->checkDbConnection();
        $this->getDatabaseDrive();
        $this->getWindowsDumpBinary();

        if (defined('DB_CONFIG')) {
            $this->dump->setDbName(getenv('DB_NAME'));
            $this->dump->setUserName(getenv('DB_USER'));
            $this->dump->setPassword(getenv('DB_PASS'));
        }
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
            /* switch (DB_CONFIG['DRIVE']) {
                case 'mysql':
                    $this->dump = MySql::create();
                    break;

                case 'pgsql':
                    $this->dump = PostgreSql::create();
                    break;

                case 'sqlite':
                    $this->dump = Sqlite::create();
                    break;

                default:
                    throw new DumpException("Database drive not valid");
                    break;
            } */

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
                    $this->dump->setDumpBinaryPath($this->yaml_data['dump_windows']['mysql']);
                    break;

                case 'pgsql':
                    $this->dump->setDumpBinaryPath($this->yaml_data['dump_windows']['pgsql']);
                    break;

                case 'sqlite':
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
