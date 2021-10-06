<?php

namespace Solital\Components\Model;

use Solital\Core\Database\Create\Model;

class NameDefault extends Model
{
    /**
     * Construct
     */
    public function __construct()
    {
        $this->table = "";
        $this->primary_key = "";
        $this->columns = [];
    }

    /**
     * @return null|array
     */
    public function listAll()
    {
        return $this->instance()->select()->build("ALL");
    }

    /**
     * @return null|array
     */
    public function list(int $id)
    {
        return $this->instance()->select($id)->build("ONLY");
    }

    /**
     * @return mixed
     */
    public function insert()
    {
        return $this->instance->insert([]);
    }

    /**
     * @return mixed
     */
    public function update(int $id)
    {
        return $this->instance->update($this->columns, [], $id);
    }

    /**
     * @return mixed
     */
    public function delete(int $id)
    {
        return $this->instance()->delete($id)->build();
    }
}
