<?php

namespace Solital\Core\Http\Controller;

interface ResourceControllerInterface
{
    /**
     * @return mixed
     */
    public function index(): mixed;

    /**
     * @param mixed $id
     * @return mixed
     */
    public function show(mixed $id): mixed;

    /**
     * @return mixed
     */
    public function store(): mixed;

    /**
     * @return mixed
     */
    public function create(): mixed;

    /**
     * @param mixed $id
     * @return mixed
     */
    public function edit(mixed $id): mixed;

    /**
     * @param mixed $id
     * @return mixed
     */
    public function update(mixed $id): mixed;

    /**
     * @param mixed $id
     * @return mixed
     */
    public function destroy(mixed $id): mixed;

}