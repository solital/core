<?php

namespace Solital\Core\Database\Seeds;

use Solital\Core\Console\MessageTrait;
use Solital\Core\Database\Seeds\Exception\SeedsException;
use Solital\Core\Kernel\Application;
use Solital\Core\FileSystem\HandleFiles;
use Nette\PhpGenerator\{ClassType, Method};

class Seeder
{
    use MessageTrait;

    /**
     * @var string
     */
    private string $seeds_dir;

    /**
     * @var bool
     */
    private bool $debug;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->debug = Application::DEBUG;
        $this->seeds_dir = Application::getRootApp('Database/seeds/', $this->debug);
    }

    /**
     * @param string $seed_name
     * 
     * @return Seeder
     */
    public function create(string $seed_name): Seeder
    {
        $folder = new HandleFiles();

        $run_method = (new Method('run'))
            ->setPublic()
            ->setBody("// ...")
            ->addComment("Run a Seed");

        $data = (new ClassType($seed_name))
            ->setExtends(Seeder::class)
            ->addMember($run_method)
            ->addComment("@generated class generated using Vinci Console");

        $seed_file_name = $this->seeds_dir . $seed_name . ".php";

        if (file_exists($seed_file_name)) {
            $this->error("Seed '{$seed_name}' already exists. Aborting!")->print()->break()->exit();
        }

        try {
            if (!is_dir($this->seeds_dir)) {
                $folder->create($this->seeds_dir);
            }

            file_put_contents($seed_file_name, "<?php\n\n" . $data);

            $this->success("Seeder created successfully!")->print()->break();
        } catch (SeedsException $e) {
            $this->error("Seeder not created: " . $e->getMessage())->print()->break()->exit();
        }

        return $this;
    }

    /**
     * @param object $options
     * 
     * @return Seeder
     */
    public function executeSeeds(object $options): Seeder
    {
        $start_time = microtime(true);

        if (isset($options->class)) {
            $this->warning("Running seeder: " . $this->seeds_dir . $options->class)->print()->break();

            $instance = $this->initiateSeeder($this->seeds_dir . $options->class . ".php");
            $instance->run();

            $this->success("Seeder executed: " . $this->seeds_dir . $options->class)->print()->break();
        } else {
            $handle = new HandleFiles();
            $seeder = $handle->folder($this->seeds_dir)->files();

            if (empty($seeder)) {
                $this->success("No seeds found")->print()->break();
            } else {
                foreach ($seeder as $seeder) {
                    $this->warning("Running seeder: " . $seeder)->print()->break();

                    $instance = $this->initiateSeeder($seeder);
                    $instance->run();

                    $this->success("Seeder executed: " . $seeder)->print()->break();
                }
            }
        }

        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time);

        echo PHP_EOL;
        $this->success("Seeds performed on: " . $execution_time . " sec")->print()->break()->exit();

        return $this;
    }

    /**
     * @param string $seeder
     * 
     * @return mixed
     */
    private function initiateSeeder(string $seeder): mixed
    {
        if (file_exists($seeder)) {
            require_once $this->seeds_dir . basename($seeder);
            $class = str_replace(".php", "", basename($seeder));

            return new $class();
        }

        return null;
    }

    /**
     * @param string|array $seed_name
     * 
     * @return Seeder
     */
    public function call(string|array $seed_name): Seeder
    {
        if (is_string($seed_name)) {
            $instance = $this->initiateSeeder($this->seeds_dir . $seed_name . ".php");
            $instance->run();
        } elseif (is_array($seed_name)) {
            foreach ($seed_name as $seeds) {
                $instance = $this->initiateSeeder($this->seeds_dir . $seeds . ".php");
                $instance->run();
            }
        }

        return $this;
    }
}
