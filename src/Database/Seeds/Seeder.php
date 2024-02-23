<?php

namespace Solital\Core\Database\Seeds;

use Solital\Core\Console\MessageTrait;
use Solital\Core\Database\Seeds\Exception\SeedsException;
use Solital\Core\Kernel\Application;
use Solital\Core\FileSystem\HandleFiles;
use Nette\PhpGenerator\{ClassType, Method, PhpNamespace};

class Seeder
{
    use MessageTrait;

    /**
     * @var string
     */
    private string $seeds_dir;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->seeds_dir = Application::getRootApp('Database/seeds/', Application::DEBUG);
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

        $class = (new ClassType($seed_name))
            ->setExtends(Seeder::class)
            ->addMember($run_method)
            ->addComment("@generated class generated using Vinci Console");

        $data = (new PhpNamespace("Solital\Database\seeds"))
            ->add($class)
            ->addUse(Seeder::class);

        $seed_file_name = $this->seeds_dir . $seed_name . ".php";

        if (file_exists($seed_file_name)) {
            $this->error("Seed '{$seed_name}' already exists!")->print()->break();
            return $this;
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
            $this->warning("Running seeder: " . $options->class)->print()->break();

            $instance = $this->initiateSeeder($this->seeds_dir . $options->class . '.php');
            $instance->run();

            $this->success("Seeder executed: " . $options->class)->print()->break();
        } else {
            $handle = Application::provider('handler-files');
            $seeder = $handle->folder($this->seeds_dir)->files();

            if (empty($seeder)) {
                $this->success("No seeds found")->print()->break();
            } else {
                foreach ($seeder as $seeder) {
                    $this->warning("Running seeder: " . basename((string) $seeder))->print()->break();

                    $instance = $this->initiateSeeder($seeder);
                    $instance->run();

                    $this->success("Seeder executed: " . basename((string) $seeder))->print()->break();
                }
            }
        }

        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time);

        echo PHP_EOL;
        $this->success("Seeds performed on: " . $execution_time . " sec")->print()->break();

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
            $seeder_replace = str_replace(".php", "", basename($seeder));
            $class = 'Solital\Database\seeds\\' . $seeder_replace;

            $class = new \ReflectionClass($class);
            return $class->newInstance();
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
            $instance = $this->initiateSeeder($this->seeds_dir . $seed_name);
            $instance->run();
        }

        if (is_array($seed_name)) {
            foreach ($seed_name as $seeds) {
                $instance = $this->initiateSeeder($this->seeds_dir . $seeds);
                $instance->run();
            }
        }

        return $this;
    }
}
