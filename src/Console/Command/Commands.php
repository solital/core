<?php

namespace Solital\Core\Console\Command;

use Solital\Core\Console\Style\Colors;
use Solital\Core\Resource\FileSystem\HandleFiles;

class Commands
{
    /**
     * @var bool
     */
    protected bool $debug;

    /**
     * @var string
     */
    private string $resource;

    /**
     * @var string
     */
    protected string $dir = '';

    /**
     * @var string
     */
    private string $type;

    /**
     * @var instance
     */
    protected $color;

    /**
     * @var string
     */
    private string $template;

    /**
     * @var array
     */
    private array $system_files = [
        'Controller.php'
    ];

    /**
     * @var string
     */
    private string $ext;

    /**
     * @param bool $debug
     */
    public function __construct($debug = false)
    {
        $this->debug = $debug;

        if ($this->debug == true) {
            $this->dir = "." . DIRECTORY_SEPARATOR . "tests" . DIRECTORY_SEPARATOR . "files_test" . DIRECTORY_SEPARATOR;
        }

        $this->template = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR;
        $this->color = new Colors();
    }

    /**
     * @return bool
     */
    public function createComponent(): bool
    {
        $this->checkExtDir();

        if (is_dir($this->dir)) {
            $res = (new HandleFiles())->folder($this->dir)->fileExists($this->resource);

            if ($res == true) {
                $msg = $this->color->stringColor("Error: there is a file with the same name", "yellow", "red", true);
                print_r($msg);

                return false;
            }

            $this->putContents($this->template);

            $msg = $this->color->stringColor("File successfully created!", "green", null, true);
            print_r($msg);

            return true;
        }

        return false;
    }

    /**
     * @param string $dir
     * 
     * @return bool
     */
    public function removeComponent($dir = ''): bool
    {
        $this->checkExtDir();

        if ($dir != '') {
            $this->dir = $dir;
        }

        $file = $this->dir . $this->resource;

        if (is_file($file)) {
            unlink($file);

            $msg = $this->color->stringColor("Component removed successfully!", "green", null, true);

            print_r($msg);

            return true;
        }

        $msg = $this->color->stringColor("Error: the component could not be removed!", "yellow", "red", true);

        print_r($msg);

        return false;
    }

    /**
     * @return bool
     */
    public function createResource(): bool
    {
        $this->checkExtDir();

        if (is_dir($this->dir)) {
            $res = (new HandleFiles())->folder($this->dir)->fileExists($this->resource);

            if ($res == true) {
                $msg = $this->color->stringColor("Error: there is a file with the same name", "yellow", "red", true);

                print_r($msg);

                return false;
            }

            file_put_contents($this->dir . $this->resource, "");

            $msg = $this->color->stringColor("File successfully created!", "green", null, true);

            print_r($msg);

            return true;
        }

        return false;
    }

    /**
     * @param string $dir
     * 
     * @return bool
     */
    public function removeResource($dir = ''): bool
    {
        $this->checkExtDir();

        if ($dir != '') {
            $this->dir = $dir;
        }

        $file = $this->dir . $this->resource;

        if (is_file($file)) {
            if (in_array($this->resource, $this->system_files)) {
                $msg = $this->color->stringColor("Error: system component cannot be deleted!", "yellow", "red", true);

                print_r($msg);

                return false;
            } else {
                unlink($file);

                $msg = $this->color->stringColor("Component removed successfully!", "green", null, true);

                print_r($msg);

                return true;
            }
        }

        $msg = $this->color->stringColor("Error: the file could not be removed!", "yellow", "red", true);

        print_r($msg);

        return false;
    }

    /**
     * @param string $cssName
     * 
     * @return Commands
     */
    public function css(string $cssName): Commands
    {
        $this->resource = $cssName . ".css";
        $this->type = "css";
        $this->template = $this->template . "CSSName.php";

        return $this;
    }

    /**
     * @param string $jsName
     * 
     * @return Commands
     */
    public function js(string $jsName): Commands
    {
        $this->resource = $jsName . ".js";
        $this->type = "js";
        $this->template = $this->template . "JSName.php";

        return $this;
    }

    /**
     * @param string $viewName
     * 
     * @return Commands
     */
    public function view(string $viewName): Commands
    {
        $this->resource = $viewName . ".php";
        $this->type = "view";
        $this->template = $this->template . "ViewName.php";

        return $this;
    }

    /**
     * @param string $controller
     * 
     * @return Commands
     */
    public function controller(string $controller): Commands
    {
        $this->resource = $controller . ".php";
        $this->type = "controller";
        $this->template = $this->template . "ControllerName.php";

        return $this;
    }

    /**
     * @param string $model
     * 
     * @return Commands
     */
    public function model(string $model): Commands
    {
        $this->resource = $model . ".php";
        $this->type = "model";
        $this->template = $this->template . "ModelName.php";

        return $this;
    }

    /**
     * @param string $viewName
     * 
     * @return Commands
     */
    public function router(string $routerName): Commands
    {
        $this->resource = $routerName . ".php";
        $this->type = "router";
        $this->template = $this->template . "RouterName.php";

        return $this;
    }

    /**
     * @param string $fileName
     * @param string $dir
     * 
     * @return Commands
     */
    public function file(string $fileName, string $dir): Commands
    {
        $this->resource = $fileName;

        if ($this->debug != true) {
            $this->dir = $dir;
        } else {
            $this->dir = $this->dir;
        }

        $this->type = "file";

        return $this;
    }

    /**
     * @return Commands
     */
    private function putContents($template): Commands
    {
        if ($this->debug != true) {
            if ($this->type == "controller") {
                $this->dir = "." . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "Components" . DIRECTORY_SEPARATOR . "Controller" . DIRECTORY_SEPARATOR;
            } elseif ($this->type == "model") {
                $this->dir = "." . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "Components" . DIRECTORY_SEPARATOR . "Model" . DIRECTORY_SEPARATOR;
            } elseif ($this->type == "css") {
                $this->dir = "." . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "_css" . DIRECTORY_SEPARATOR;
            } elseif ($this->type == "js") {
                $this->dir = "." . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "_js" . DIRECTORY_SEPARATOR;
            } elseif ($this->type == "view") {
                $this->dir = "." . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR;
            } elseif ($this->type == "router") {
                $this->dir = "." . DIRECTORY_SEPARATOR . "routers" . DIRECTORY_SEPARATOR;
            }
        }

        $resource = explode(".", $this->resource);
        $output_template = file_get_contents($template);

        if (strpos($output_template, 'NameDefault') !== false) {
            $output_template = str_replace('NameDefault', $resource[0], $output_template);
        }

        file_put_contents($this->dir . $this->resource, $output_template);

        return $this;
    }

    /**
     * @return Commands
     */
    protected function checkExtDir(): Commands
    {
        $this->ext = pathinfo($this->resource)['extension'];

        if ($this->debug == true) {
            return $this;
        } elseif ($this->type == "controller") {
            $this->dir = "." . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "Components" . DIRECTORY_SEPARATOR . "Controller" . DIRECTORY_SEPARATOR;

            return $this;
        } elseif ($this->type == "model") {
            $this->dir = "." . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "Components" . DIRECTORY_SEPARATOR . "Model" . DIRECTORY_SEPARATOR;

            return $this;
        } elseif ($this->type == "css") {
            $this->dir = "." . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "_css" . DIRECTORY_SEPARATOR;

            return $this;
        } elseif ($this->type == "js") {
            $this->dir = "." . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "_js" . DIRECTORY_SEPARATOR;

            return $this;
        } elseif ($this->type == "view") {
            $this->dir = "." . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR;

            return $this;
        } elseif ($this->type == "router") {
            $this->dir = "." . DIRECTORY_SEPARATOR . "routers" . DIRECTORY_SEPARATOR;

            return $this;
        }

        return $this;
    }
}
