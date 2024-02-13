<?php

namespace Solital\Core\Kernel\Console\Commands;

use Katrina\Sql\KatrinaStatement;
use Solital\Core\Console\Command;
use Solital\Core\Console\TableBuilder;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Kernel\Application;

class ListDatabase extends Command implements CommandInterface
{
    /**
     * @var string
     */
    protected string $command = "db:list";

    /**
     * @var array
     */
    protected array $arguments = ["name_column"];

    /**
     * @var string
     */
    protected string $description = "List data from a database table";

    /**
     * @var string
     */
    private string $where = '';

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    public function handle(object $arguments, object $options): mixed
    {
        Application::connectionDatabase();

        $all_values = [];

        if (isset($options->limit)) {
            $this->where = " LIMIT " . $options->limit;
        } else {
            $this->where = " LIMIT 10";
        }

        if (!isset($arguments->name_column)) {
            $data = KatrinaStatement::executeQuery("SHOW TABLES", true);
        } else {
            $data = KatrinaStatement::executeQuery("SELECT * FROM " . $arguments->name_column . $this->where, true);
        }

        if (empty($data)) {
            $this->success('Table "' . $arguments->name_column . '" is empty')->print()->exit();
        }

        foreach ($data as $data) {
            $keys = array_keys((array)$data);
            $values = array_values((array)$data);
            array_push($all_values, $values);
        }

        (new TableBuilder())->getTableRows($all_values, $keys);

        return $this;
    }
}
