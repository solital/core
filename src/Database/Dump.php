<?php

namespace Solital\Core\Database;

use Symfony\Component\Yaml\Yaml;
use Solital\Core\Console\{Command, MessageTrait};
use Solital\Core\Kernel\Application;

class Dump extends Command
{
    use MessageTrait;

    /**
     * @var array
     */
    private array $data;

    /**
     * @var string
     */
    private string $database_drive;

    /**
     * @var string
     */
    private string $drive;

    /**
     * @var string
     */
    private string $database;

    /**
     * @var string
     */
    private string $host;

    /**
     * @var string
     */
    private string $username;

    /**
     * @var string
     */
    private string $password;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->data = Yaml::parseFile(Application::getDirConfigFiles(5) . '/database.yaml');

        Application::connectionDatabase();

        $this->setDrive(DB_CONFIG['DRIVE']);
        $this->setDatabase(DB_CONFIG['DBNAME']);
        $this->setHost(DB_CONFIG['HOST']);
        $this->setUsername(DB_CONFIG['USER']);
        $this->setPassword(DB_CONFIG['PASS']);
    }

    /**
     * Get the value of drive
     */
    public function getDrive()
    {
        return $this->drive;
    }

    /**
     * Set the value of drive
     *
     * @return  self
     */
    public function setDrive($drive)
    {
        $this->drive = $drive;

        return $this;
    }

    /**
     * Get the value of database
     *
     * @return  string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Set the value of database
     *
     * @param  string  $database
     *
     * @return  self
     */
    public function setDatabase(string $database)
    {
        $this->database = $database;

        return $this;
    }

    /**
     * Get the value of host
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set the value of host
     *
     * @return  self
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get the value of username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the value of username
     *
     * @return  self
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get the value of password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param string $path
     * 
     * @return Dump
     */
    public function dumpDatabase(string $path): Dump
    {
        $dump_file = "dump-" . date('Ymd-His') . ".sql";
        $db_drive = $this->getDatabaseDrive();
        $pass = $this->getPassword() ? "-p " . $this->getPassword() : "";

        if ($this->getDrive() == "mysql") {
            $cmd = "{$db_drive} -u {$this->getUsername()} " . $pass . " {$this->getDatabase()} > " . $path . DIRECTORY_SEPARATOR . $dump_file;
        } elseif ($this->getDrive() == "pgsql") {
            $path = str_replace("\\", "\\\\", $path);
            $cmd = "{$db_drive} -U {$this->getUsername()} -h {$this->getHost()} -d {$this->getDatabase()} -f '{$path}" . "{$dump_file}' 2>&1";
        } else {
            $this->error("Drive not found")->print()->break()->exit();
        }

        #$cmd = escapeshellarg($cmd);
        #var_dump($cmd);exit;

        $this->execInBackground($cmd);
        $this->success("Dump created successfully!")->print()->break()->exit();

        return $this;
    }

    /**
     * @return string
     */
    private function getDatabaseDrive(): string
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            if ($this->getDrive() == "mysql") {
                $this->database_drive = $this->data['dump_windows']['mysql'];
            } elseif ($this->getDrive() == "pgsql") {
                $this->database_drive = $this->data['dump_windows']['pgsql'];
            } elseif ($this->getDrive() == "sqlite") {
                $this->database_drive = $this->data['dump_windows']['sqlite'];
            }
        } else {
            if ($this->getDrive() == "mysql") {
                $this->database_drive = "mysqldump";
            } elseif ($this->getDrive() == "pgsql") {
                $this->database_drive = "pg_dump";
            } elseif ($this->getDrive() == "sqlite") {
                $this->database_drive = "sqlite";
            }
        }

        return $this->database_drive;
    }
}
