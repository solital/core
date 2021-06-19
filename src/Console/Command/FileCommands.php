<?php

namespace Solital\Core\Console\Command;

use Solital\Core\Wolf\Wolf;
use Solital\Core\Database\Create\Create;
use Solital\Core\Console\Command\Commands;
use Solital\Core\Console\Command\DatabaseCommand;
use Solital\Core\Resource\FileSystem\HandleFiles;

class FileCommands extends Commands
{
    /**
     * @var string
     */
    private $dir_controller;

    /**
     * @var string
     */
    private $dir_view;

    /**
     * @var string
     */
    private $dir_router;

    /**
     * @var HandleFiles
     */
    private $handle;

    /**
     * @param bool $debug
     */
    public function __construct($debug = false)
    {
        parent::__construct($debug);

        $this->handle = new HandleFiles();

        if ($this->debug == true) {
            $this->dir_controller = $this->dir;
            $this->dir_view = $this->dir;
            $this->dir_router = $this->dir;
        } else {
            $this->dir_controller = SITE_ROOT_VINCI . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "Components" . DIRECTORY_SEPARATOR . "Controller" . DIRECTORY_SEPARATOR . "Auth" . DIRECTORY_SEPARATOR;
            $this->dir_view = SITE_ROOT_VINCI . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . "auth" . DIRECTORY_SEPARATOR;
            $this->dir_router = SITE_ROOT_VINCI . DIRECTORY_SEPARATOR . "routers" . DIRECTORY_SEPARATOR;

            if (!is_dir($this->dir_controller)) {
                \mkdir($this->dir_controller);
            }

            if (!is_dir($this->dir_view)) {
                \mkdir($this->dir_view);
            }
        }
    }

    /**
     * @return bool
     */
    public function login(): bool
    {
        (new DatabaseCommand())->checkConnection();

        if ($this->debug != true) {
            if (!is_dir($this->dir_view)) {
                \mkdir($this->dir_view);
            }
        }

        if ($this->debug != true) {
            $file_login = $this->dir_controller . "LoginController.php";
            $file_login_view = $this->dir_view . "login.php";
            $file_dashboard_view = $this->dir_view . "dashboard.php";
            $file_login_router = $this->dir_router . "login-routers.php";
        } else {
            $file_login = $this->dir . "LoginController.php";
            $file_login_view = $this->dir . "login.php";
            $file_dashboard_view = $this->dir . "dashboard.php";
            $file_login_router  = $this->dir . "login-routers.php";
        }

        if (is_file($file_login) && is_file($file_login_view) && is_file($file_dashboard_view) && is_file($file_login_router)) {
            $msg = $this->color->stringColor("ERROR: Login files already exist!", "yellow", "red", true);
            print_r($msg);

            die;
        }

        $dir_login_controller = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . 'LoginComponents' . DIRECTORY_SEPARATOR . 'LoginController.php';
        $dir_login_form = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . 'LoginComponents' . DIRECTORY_SEPARATOR . 'login-form.php';
        $dir_login_dashboard = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . 'LoginComponents' . DIRECTORY_SEPARATOR . 'login-dashboard.php';
        $dir_login_header = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . 'header.php';
        $dir_login_footer = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . 'footer.php';
        $dir_login_routers = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . 'LoginComponents' . DIRECTORY_SEPARATOR . 'login-routers.php';

        $this->createFile($dir_login_controller, $this->dir_controller, 'LoginController.php');
        $this->createFile($dir_login_form, $this->dir_view, 'login.php');
        $this->createFile($dir_login_dashboard, $this->dir_view, 'dashboard.php');
        $this->createFile($dir_login_header, $this->dir_view, 'header.php');
        $this->createFile($dir_login_footer, $this->dir_view, 'footer.php');
        $this->createFile($dir_login_routers, $this->dir_router, 'login-routers.php');

        (new Create())->userAuth();

        $msg = $this->color->stringColor("LOGIN: Files successfully created! ", "green", null, true);
        print_r($msg);

        return false;
    }

    /**
     * @return bool
     */
    public function removeLogin(): bool
    {
        $this->confirmDialog("Are you sure you want to delete the login components? (this process cannot be undone)? [Y/N] ");

        if ($this->debug != true) {
            $file_login = $this->dir_controller . "LoginController.php";
            $file_login_view = $this->dir_view . "login.php";
            $file_dashboard_view = $this->dir_view . "dashboard.php";
            $file_login_router = $this->dir_router . "login-routers.php";
        } else {
            $file_login = $this->dir . "LoginController.php";
            $file_login_view = $this->dir . "login.php";
            $file_dashboard_view = $this->dir . "dashboard.php";
            $file_login_router  = $this->dir . "login-routers.php";
        }

        $res = $this->removeFile($file_login, $file_login_view, $file_dashboard_view, $file_login_router);

        if ($res == true) {
            $msg = $this->color->stringColor("LOGIN: Files successfully removed! ", "green", null, true);
            print_r($msg);

            return true;
        } else {
            $msg = $this->color->stringColor("Error: it wasn't possible to remove the files", "yellow", "red", true);
            print_r($msg);

            return false;
        }
    }

    /**
     * @return bool
     */
    public function forgot(): bool
    {
        if ($this->debug != true) {
            $file_forgot_controller = $this->dir_controller . 'ForgotController.php';
            $file_forgot_view = $this->dir_view . 'forgot-form.php';
            $file_forgot_change = $this->dir_view . 'change-pass-form.php';
            $file_forgot_header = $this->dir_view . 'header.php';
            $file_forgot_footer = $this->dir_view . 'footer.php';
            $file_forgot_routers = $this->dir_router . 'forgot-routers.php';
        } else {
            $file_forgot_controller = $this->dir . "ForgotController.php";
            $file_forgot_view = $this->dir . "forgot-form.php";
            $file_forgot_change = $this->dir . "change-pass-form.php";
            $file_forgot_header = $this->dir . "header.php";
            $file_forgot_footer = $this->dir . "footer.php";
            $file_forgot_routers = $this->dir . "forgot-routers.php";
        }

        $dir_forgot_controller = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . 'ForgotComponents' . DIRECTORY_SEPARATOR . 'ForgotController.php';
        $dir_forgot_view = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . 'ForgotComponents' . DIRECTORY_SEPARATOR . 'forgot-form.php';
        $dir_forgot_header = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . 'header.php';
        $dir_forgot_footer = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . 'footer.php';
        $dir_forgot_change = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . 'ForgotComponents' . DIRECTORY_SEPARATOR . 'change-pass-form.php';
        $dir_forgot_routers = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . 'ForgotComponents' . DIRECTORY_SEPARATOR . 'forgot-routers.php';

        $this->createFile($dir_forgot_controller, $this->dir_controller, 'ForgotController.php');
        $this->createFile($dir_forgot_view, $this->dir_view, 'forgot-form.php');
        $this->createFile($dir_forgot_change, $this->dir_view, 'change-pass-form.php');
        $this->createFile($dir_forgot_header, $this->dir_view, 'header.php');
        $this->createFile($dir_forgot_footer, $this->dir_view, 'footer.php');
        $this->createFile($dir_forgot_routers, $this->dir_router, 'forgot-routers.php');

        $msg = $this->color->stringColor("FORGOT: Files successfully created! ", "green", null, true);
        print_r($msg);

        return false;
    }

    /**
     * @return bool
     */
    public function removeForgot(): bool
    {
        $this->confirmDialog("Are you sure you want to delete the forgot password components? (this process cannot be undone)? [Y/N] ");

        if ($this->debug != true) {
            $file_forgot_controller = $this->dir_controller . 'ForgotController.php';
            $file_forgot_view = $this->dir_view . 'forgot-form.php';
            $file_forgot_change = $this->dir_view . 'change-pass-form.php';
            $file_forgot_routers = $this->dir_router . 'forgot-routers.php';
        } else {
            $file_forgot_controller = $this->dir . "ForgotController.php";
            $file_forgot_view = $this->dir . "forgot-form.php";
            $file_forgot_change = $this->dir . "change-pass-form.php";
            $file_forgot_routers = $this->dir . "forgot-routers.php";
        }

        $res = $this->removeFile($file_forgot_controller, $file_forgot_view, $file_forgot_change, $file_forgot_routers);

        if ($res == true) {
            $msg = $this->color->stringColor("FORGOT: Files successfully removed! ", "green", null, true);
            print_r($msg);

            return true;
        }

        $msg = $this->color->stringColor("Error: it wasn't possible to remove the files", "yellow", "red", true);
        print_r($msg);

        return false;
    }

    /**
     * @return FileCommands
     */
    public function minifyCss(): FileCommands
    {
        Wolf::minify()->style();

        $msg = $this->color->stringColor("CSS minified successfully!", "green", null, true);
        print_r($msg);

        return $this;
    }

    /**
     * @return FileCommands
     */
    public function minifyJs(): FileCommands
    {
        Wolf::minify()->script();

        $msg = $this->color->stringColor("JavaScript minified successfully!", "green", null, true);
        print_r($msg);

        return $this;
    }

    /**
     * @return FileCommands
     */
    public function minifyAll(): FileCommands
    {
        Wolf::minify()->all();

        $msg = $this->color->stringColor("CSS and JavaScript minified successfully!", "green", null, true);
        print_r($msg);

        return $this;
    }

    /**
     * @return FileCommands
     */
    public function confirmDialog(string $msg): FileCommands
    {
        $msg = $this->color->stringColor($msg, "white", null);
        print_r($msg);

        $stdin = fopen("php://stdin", "rb");
        $res = fgets($stdin);
        $res = strtoupper($res);

        if (\trim($res) == "Y") {
            return $this;
        } else if (\trim($res) == "N") {
            $msg = $this->color->stringColor("Aborted", "white", null, true);
            print_r($msg);

            die;
        } else {
            $msg = $this->color->stringColor("ERROR: Invalid option!", "yellow", "red", true);
            print_r($msg);

            die;
        }

        return $this;
    }

    /**
     * @param string $dir
     * @param string $output_dir
     * 
     * @return null|mixed
     */
    private function createFile(string $dir, string $output_dir, string $file)
    {
        $res = $this->handle->folder($output_dir)->fileExists($file);

        if ($res != true) {
            $res = $this->handle->getAndPutContents($dir, $output_dir . $file);

            return $res;
        }

        return null;
    }

    /**
     * @param mixed ...$files
     * 
     * @return bool
     */
    private function removeFile(...$files): bool
    {
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } else {
                return false;
            }
        }

        $this->handle->remove($this->dir_controller);
        #$this->handle->remove($this->dir_view);

        return true;
    }
}
