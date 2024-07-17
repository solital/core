<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Console\Command;
use Solital\Core\Console\Output\ConsoleOutput;
use Solital\Core\Kernel\{Application, Console\HelpersTrait, DebugCore};
use Solital\Core\Kernel\Model\AuthModel;

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
    protected string $description = "Create 'Login' and 'Forgot Password' structures";

    /**
     * @var array
     */
    protected array $options = ["--login", "--forgot", "--remove"];

    /**
     * @var string
     */
    private string $controller_dir = '';

    /**
     * @var string
     */
    private string $middleware_dir = '';

    /**
     * @var string
     */
    private string $route_dir = '';

    /**
     * @var string
     */
    private string $view_dir = '';

    /**
     * @var array
     */
    private array $components = [];

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        Application::connectionDatabase();
        $this->getAuthFolders();

        $handle_files = Application::provider('handler-file');
        $this->createUserAuth();

        $root_dir = Application::getRootCore('/Kernel/Console/Templates/');

        if (isset($options->login)) {
            $login_components = $handle_files->folder($root_dir . 'LoginComponents')->files();

            if (isset($options->remove)) {
                $this->removeAuthComponent([
                    $this->middleware_dir . 'AuthMiddleware.php',
                    $this->controller_dir . 'LoginController.php',
                    $this->route_dir . 'auth-login-routers.php',
                    $this->view_dir . 'auth-dashboard.php',
                    $this->view_dir . 'auth-form.php'
                ]);

                return true;
            }

            $this->createLoginSkeleton($login_components);
            return true;
        }

        if (isset($options->forgot)) {
            $forgot_components = $handle_files->folder($root_dir . 'ForgotComponents')->files();

            if (isset($options->remove)) {
                $this->removeAuthComponent([
                    $this->controller_dir . 'ForgotController.php',
                    $this->route_dir . 'forgot-routers.php',
                    $this->view_dir . 'forgot-form.php',
                    $this->view_dir . 'forgot-change-pass.php'
                ]);

                return true;
            }

            $this->createForgotSkeleton($forgot_components);
            return true;
        }

        return $this;
    }

    /**
     * @param array $components
     * 
     * @return MakeAuth
     */
    private function createLoginSkeleton(array $components): MakeAuth
    {
        $view_dir = [
            $components[0], $components[1]
        ];

        $components = [
            'route_dir' => $components[2],
            'middleware_dir' => $components[3],
            'controller_dir' => $components[4]
        ];

        $this->generateAuthTemplate($components, $view_dir);
        ConsoleOutput::success("Login components created successfully!")->print()->break();

        return $this;
    }

    /**
     * @param array $components
     * 
     * @return MakeAuth
     */
    private function createForgotSkeleton(array $components): MakeAuth
    {
        $view_dir = [
            $components[0], $components[1]
        ];

        $components = [
            'route_dir' => $components[2],
            'controller_dir' => $components[3]
        ];

        $this->generateAuthTemplate($components, $view_dir);
        ConsoleOutput::success("Forgot components created successfully!")->print()->break();

        return $this;
    }

    /**
     * Generate header and footer components in Auth view
     * 
     * @return MakeAuth
     */
    private function createHeader(): MakeAuth
    {
        $header_template = Application::getConsoleComponent('header.php');
        $footer_template = Application::getConsoleComponent('footer.php');

        $this->createAuthComponents($this->view_dir, $header_template, 'header.php');
        $this->createAuthComponents($this->view_dir, $footer_template, 'footer.php');

        return $this;
    }

    /**
     * Generate Auth components
     *
     * @param array $components
     * @param array $view_dir
     * 
     * @return MakeAuth
     */
    private function generateAuthTemplate(array $components, array $view_dir): MakeAuth
    {
        foreach ($components as $key => $component) {
            $class = new \ReflectionClass($this);
            $property = $class->getProperty($key)->getValue($this);
            $this->createAuthComponents($property, $component, basename($component));
        }

        foreach ($view_dir as $view) {
            $this->createAuthComponents($this->view_dir, $view, basename($view));
        }

        $this->createHeader();
        return $this;
    }

    /**
     * @return MakeAuth
     */
    public function createUserAuth(): MakeAuth
    {
        $users = AuthModel::createUserTable();

        if (empty($users)) {
            $db = new AuthModel();
            $db->username = 'solital@email.com';
            $db->password = pass_hash('solital');
            $db->save();

            ConsoleOutput::success("User created successfully!")->print()->break();
        } else {
            ConsoleOutput::success("User already exists!")->print()->break();
        }

        return $this;
    }

    /**
     * @return void
     */
    private function getAuthFolders(): void
    {
        $this->controller_dir = Application::getRootApp('Components/Controller/Auth/', DebugCore::isCoreDebugEnabled());
        $this->middleware_dir = Application::getRootApp('Middleware/', DebugCore::isCoreDebugEnabled());
        $this->route_dir = Application::getRoot('routers/', DebugCore::isCoreDebugEnabled());
        $this->view_dir = Application::getRoot('resources/view/auth/', DebugCore::isCoreDebugEnabled());
    }
}
