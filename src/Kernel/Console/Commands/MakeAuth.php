<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Auth\AuthDatabase;
use Solital\Core\Console\{Command, Interface\CommandInterface};
use Solital\Core\Kernel\{Application, Console\HelpersTrait};

class MakeAuth extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "auth:skeleton";

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var string
     */
    protected string $description = "Create Login and 'Forgot Password' structures";

    /**
     * @var string
     */
    private string $controller_dir;

    /**
     * @var string
     */
    private string $route_dir;

    /**
     * @var string
     */
    private string $view_dir;

    /**
     * Construct
     */
    public function __construct()
    {
        Application::connectionDatabase();
        $this->getAuthFolders();
    }

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    public function handle(object $arguments, object $options): mixed
    {
        $header_template = Application::getConsoleComponent('header.php');
        $footer_template = Application::getConsoleComponent('footer.php');

        $login_template_controller = Application::getConsoleComponent('LoginComponents/LoginController.php');
        $login_template_route = Application::getConsoleComponent('LoginComponents/login-routers.php');
        $login_template_dashboard = Application::getConsoleComponent('LoginComponents/auth-dashboard.php');
        $login_template_form = Application::getConsoleComponent('LoginComponents/auth-form.php');

        $forgot_template_controller = Application::getConsoleComponent('ForgotComponents/ForgotController.php');
        $forgot_template_route = Application::getConsoleComponent('ForgotComponents/forgot-routers.php');
        $forgot_template_form = Application::getConsoleComponent('ForgotComponents/forgot-form.php');
        $forgot_template_pass = Application::getConsoleComponent('ForgotComponents/change-pass-form.php');

        if (isset($options->user)) {
            $this->createUserAuth();

            return true;
        }

        if (isset($options->login)) {
            if (isset($options->remove)) {
                $this->removeAuthComponent([
                    $this->controller_dir . 'LoginController.php',
                    $this->route_dir . 'login.php',
                    $this->view_dir . 'auth-dashboard.php',
                    $this->view_dir . 'auth-form.php'
                ]);

                return true;
            } else {
                $this->createUserAuth();

                $this->login(
                    $login_template_controller,
                    $login_template_route,
                    $login_template_dashboard,
                    $login_template_form,
                    $header_template,
                    $footer_template
                );

                return true;
            }
        } elseif (isset($options->forgot)) {
            if (isset($options->remove)) {
                $this->removeAuthComponent([
                    $this->controller_dir . 'ForgotController.php',
                    $this->route_dir . 'forgot.php',
                    $this->view_dir . 'forgot-form.php',
                    $this->view_dir . 'change-pass-form.php'
                ]);

                return true;
            } else {
                $this->createUserAuth();

                $this->forgot(
                    $forgot_template_controller,
                    $forgot_template_route,
                    $forgot_template_form,
                    $forgot_template_pass,
                    $header_template,
                    $footer_template
                );
            }
        }

        return true;
    }

    /**
     * @param string $login_template_controller
     * @param string $login_template_route
     * @param string $login_template_dashboard
     * @param string $login_template_form
     * @param string $header_template
     * @param string $footer_template
     * 
     * @return void
     */
    public function login(
        string $login_template_controller,
        string $login_template_route,
        string $login_template_dashboard,
        string $login_template_form,
        string $header_template,
        string $footer_template
    ): void {
        $this->createAuthComponents($this->controller_dir, $login_template_controller, 'LoginController.php');
        $this->createAuthComponents($this->route_dir, $login_template_route, 'login.php');
        $this->createAuthComponents($this->view_dir, $login_template_dashboard, 'auth-dashboard.php');
        $this->createAuthComponents($this->view_dir, $login_template_form, 'auth-form.php');
        $this->createAuthComponents($this->view_dir, $header_template, 'header.php');
        $this->createAuthComponents($this->view_dir, $footer_template, 'footer.php');

        $this->success("Login components created successfully!")->print()->break();
    }

    /**
     * @param string $forgot_template_controller
     * @param string $forgot_template_route
     * @param string $forgot_template_dashboard
     * @param string $forgot_template_form
     * @param string $forgot_template_pass
     * @param string $header_template
     * @param string $footer_template
     * 
     * @return void
     */
    public function forgot(
        string $forgot_template_controller,
        string $forgot_template_route,
        string $forgot_template_form,
        string $forgot_template_pass,
        string $header_template,
        string $footer_template
    ): void {
        $this->createAuthComponents($this->controller_dir, $forgot_template_controller, 'ForgotController.php');
        $this->createAuthComponents($this->route_dir, $forgot_template_route, 'forgot.php');
        $this->createAuthComponents($this->view_dir, $forgot_template_form, 'forgot-form.php');
        $this->createAuthComponents($this->view_dir, $forgot_template_pass, 'change-pass-form.php');
        $this->createAuthComponents($this->view_dir, $header_template, 'header.php');
        $this->createAuthComponents($this->view_dir, $footer_template, 'footer.php');

        $this->success("Forgot components created successfully!")->print()->break();
    }

    /**
     * @return MakeAuth
     */
    public function createUserAuth(): MakeAuth
    {
        $users = (new AuthDatabase())->createUserTable();

        if (empty($users)) {
            $db = new AuthDatabase();
            $db->username = 'solital@email.com';
            $db->password = '$2y$10$4gjz66edZG.bNYIabcxkgerycCXYazTu8QOWKBhWZKcUr6gikxjYa'; // pass = solital
            $db->save();

            $this->success("User created successfully!")->print()->break();
        } else {
            $this->success("User already exists!")->print()->break();
        }

        return $this;
    }
}
