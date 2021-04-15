<?php

namespace Solital\Core\Console\Command;

use Solital\CustomConsole;
use Solital\Core\Database\ORMConsole;
use Solital\Core\Resource\FileSystem\HandleFiles;
use Solital\Database\SQL;

trait CustomCommandTrait
{
    /**
     * @var string
     */
    private string $dir_app;

    /**
     * @return string
     */
    private function getDirApp(): string
    {
        $this->dir_app = SITE_ROOT_VINCI . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR;

        return $this->dir_app;
    }
    /**
     * @param array $array
     * @param string $cmd
     * 
     * @return mixed
     */
    public function prepare(array $array, string $cmd)
    {
        if (array_key_exists($cmd, $array)) {
            $method = $array[$cmd];

            if (strpos($cmd, 'katrina') === false) {
                (new CustomConsole())->$method();
            } else {
                (new ORMConsole())->$method();
            }

            $msg = $this->color->stringColor("Command successfully executed!", "green", null, true);
            print_r($msg);

            die;
        } else {
            return null;
        }
    }

    /**
     * @param string $cmd
     * 
     * @return mixed
     */
    public function execCommand(string $cmd)
    {
        $res = $this->verifyKatrinaCommand($cmd);

        if (is_array($res)) {
            $this->prepare($res, $cmd);
        } else {
            return null;
        }

        return $this;
    }

    /**
     * @param string $cmd
     * 
     * @return array|null
     */
    private function verifyKatrinaCommand(string $cmd): ?array
    {
        $sql = new SQL();

        if (strpos($cmd, 'katrina') === false) {
            $res = (new CustomConsole())->execute();

            return $res;
        } else if (stripos($cmd, ":") !== false) {
            $file = explode(':', $cmd);
            $method = $file[1];

            if (method_exists($sql, $method)) {
                $sql_file = $this->getDirApp() . "Database" . DIRECTORY_SEPARATOR . "SQL.php";
                $sql_file_cache = $this->getDirApp() . "Storage" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "sql" . DIRECTORY_SEPARATOR . "cache-sql-" . date('Y-m-d-His') . ".php";

                (new HandleFiles())->getAndPutContents($sql_file, $sql_file_cache);

                $sql->$method();

                $msg = $this->color->stringColor("KATRINA: Command successfully executed!", "green", null, true);
                print_r($msg);
            } else {
                $msg = $this->color->stringColor("KATRINA: Table '$method' not found in SQL.php ", "yellow", "red", true);
                print_r($msg);
            }

            die;
        } else {
            $res = (new ORMConsole())->execute();

            return $res;
        }

        return null;
    }
}
