<?php

namespace Solital\Core\Queue;

use React\EventLoop\Loop;
use Solital\Core\Kernel\Application;
use Solital\Core\Console\MessageTrait;
use Solital\Core\Queue\Exception\QueueException;
use Solital\Core\FileSystem\HandleFiles;
use Nette\PhpGenerator\{ClassType, Method};

class Queue
{
    use MessageTrait;

    /**
     * @var string
     */
    private string $queue_dir;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->queue_dir = Application::getRootApp('Queue/', Application::DEBUG);

        if (!is_dir($this->queue_dir)) {
            (new HandleFiles)->create($this->queue_dir);
        }
    }

    /**
     * @param string $queue_name
     * 
     * @return Queue
     */
    public function create(string $queue_name): Queue
    {
        $dispatch_method = (new Method('dispatch'))
            ->setPublic()
            ->setBody("// ...")
            ->addComment("dispatch");

        $class = (new ClassType($queue_name))
            ->setExtends(Queue::class)
            ->addMember($dispatch_method)
            ->addComment("@generated class generated using Vinci Console");

        $queue_file_name = $this->queue_dir . $queue_name . ".php";

        if (file_exists($queue_file_name)) {
            $this->error("Queue '{$queue_name}' already exists. Aborting!")->print()->break()->exit();
        }

        try {
            file_put_contents($this->queue_dir . $queue_name . '.php', "<?php \n\n" . $class);

            $this->success("Queue created successfully!")->print()->break();
        } catch (QueueException $e) {
            $this->error("Queue not created: " . $e->getMessage())->print()->break()->exit();
        }

        return $this;
    }

    /**
     * @param object $options
     * 
     * @return Queue
     */
    public function executeQueues(object $options): Queue
    {
        $start_time = microtime(true);

        if (isset($options->class)) {
            $this->warning("[" . date('Y-m-d H:i:s') . "] Processing queue: " . $options->class)->print()->break();

            $instance = $this->initiateQueue($this->queue_dir . $options->class . ".php");
            $instance->dispatch();

            $this->success("[" . date('Y-m-d H:i:s') . "] Queue executed: " . $options->class)->print()->break();
        } else {
            $handle = new HandleFiles();
            $queue = $handle->folder($this->queue_dir)->files();

            foreach ($queue as $queue) {
                $loop = Loop::get();
                $this->warning("[" . date('Y-m-d H:i:s') . "] Processing queue: " . basename($queue))->print()->break();

                $instance = $this->initiateQueue($queue);
                $instance->dispatch();

                $this->success("[" . date('Y-m-d H:i:s') . "] Queue executed: " . basename($queue))->print()->break();
                $loop->run();
            }
        }

        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time);

        echo PHP_EOL;
        $this->success("Seeds performed on: " . $execution_time . " sec")->print()->break()->exit();

        return $this;
    }

    /**
     * @param string $queue
     * 
     * @return mixed
     */
    private function initiateQueue(string $queue): mixed
    {
        if (file_exists($queue)) {
            require_once $this->queue_dir . basename($queue);
            $class = str_replace(".php", "", basename($queue));

            return new $class();
        }

        return null;
    }
}
