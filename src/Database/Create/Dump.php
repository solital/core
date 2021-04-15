<?php

namespace Solital\Core\Database\Create;

use Solital\Core\Console\Style\Colors;

class Dump
{
    /**
     * @var array
     */
    private array $data;

    /**
     * @var string
     */
    private string $command;

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
     * @var string
     */
    private string $path_bkp;

    /**
     * Construct
     */
    public function __construct(string $path_bkp = '')
    {
        $this->data = $_ENV;

        if (empty($this->data)) {
            $msg = (new Colors())->stringColor("Error dumping the database! ", "yellow", "red", true);
            print_r($msg);

            die;
        }

        $this->setDrive($this->data['DB_DRIVE']);
        $this->setDatabase($this->data['DB_NAME']);
        $this->setHost($this->data['DB_HOST']);
        $this->setUsername($this->data['DB_USER']);
        $this->setPassword($this->data['DB_PASS']);

        $this->path_bkp = $path_bkp;
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
     * @return bool|null
     */
    public function dumpDatabase(string $path): ?bool
    {
        $exec = $this->getCommandSO();
        $pass = $this->getPassword() ? "-p " . $this->getPassword() : "";

        if ($this->getDrive() == "mysql") {
            $cmd = "{$exec} -u {$this->getUsername()} " . $pass . " {$this->getDatabase()} > " . $path . DIRECTORY_SEPARATOR . "dump-" . date('Ymd-His') . ".sql";
        } elseif ($this->getDrive() == "pg") {
            $cmd = "{$exec} {$this->getDatabase()} > {$this->path_bkp}";
        } else {
            die("Drive not found");
        }

        exec($cmd, $output);

        if (!$output) {
            return true;
        } else {
            return false;
        }

        return null;
    }

    /**
     * @return string
     */
    private function getCommandSO(): string
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            if ($this->getDrive() == "mysql") {
                $this->command = $this->data['MYSQL_DUMP'];
            } elseif ($this->getDrive() == "pgadmin") {
                $this->command = $this->data['PG_DUMP'];
            } elseif ($this->getDrive() == "sqlite") {
                $this->command = $this->data['SQLITE3'];
            }
        } else {
            if ($this->getDrive() == "mysql") {
                $this->command = "mysqldump";
            } elseif ($this->getDrive() == "pgadmin") {
                $this->command = "pgadmin";
            } elseif ($this->getDrive() == "sqlite") {
                $this->command = "sqlite";
            }
        }

        return $this->command;
    }
}
