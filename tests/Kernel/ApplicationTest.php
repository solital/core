<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use PHPUnit\Framework\TestCase;
use Solital\Core\Auth\Password;
use Solital\Core\FileSystem\HandleFiles;
use Solital\Core\Kernel\Application;
use Solital\Core\Wolf\Wolf;

class ApplicationTest extends TestCase
{
    public function testYaml()
    {
        $yaml_file = Application::yamlParse('auth.yaml');
        $this->assertIsArray($yaml_file);

        $yaml_file_dir = Application::yamlParse('auth.yaml', true);
        $this->assertIsString($yaml_file_dir);
        $this->assertFileExists($yaml_file_dir);
    }

    public function testProvider()
    {
        Application::getInstance();

        $provider_handle_files = Application::provider('handler-file');
        $this->assertInstanceOf(HandleFiles::class, $provider_handle_files);

        $provider_password = Application::provider('solital-password');
        $this->assertInstanceOf(Password::class, $provider_password);

        $provider_wolf = Application::provider('solital-wolf');
        $this->assertInstanceOf(Wolf::class, $provider_wolf);
    }

    public function testAddYamlValue()
    {
        $result = Application::addYamlValue('auth.yaml', 'test', 'value');
        $this->assertTrue($result);

        $values = app_get_yaml('auth.yaml');
        $this->assertEquals('value', $values['test']);
    }
}
