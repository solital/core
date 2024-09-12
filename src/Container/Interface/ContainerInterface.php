<?php

namespace Solital\Core\Container\Interface;

use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * The interface that a custom container using this container package 
 * must implement in order to properly work.
 *
 * PSR-11 compliant with array access.
 */
interface ContainerInterface extends PsrContainerInterface, \ArrayAccess
{
	/**
	 * Add an entry.
	 * 
	 * @param string $id
	 * @param mixed $value
	 */
	public function add(string $id, mixed $value);

	/**
	 * Remove an entry.
	 * 
	 * @param string $id
	 */
	public function remove(string $id);

	/**
	 * Tag a service to always return a new instance.
	 * 
	 * @param mixed $callback
	 * @return mixed
	 */
	public function factory(mixed $callback): mixed;

	/**
	 * Interpret an invokable as a literal value.
	 * 
	 * @param mixed $callback
	 * @return mixed
	 */
	public function protect(mixed $callback): mixed;

	/**
	 * Invokable to be ran after a service is created.
	 * 
	 * @param string|callable $id
	 * @param callable|null $callback
	 */
	public function extend(string|callable $id, ?callable $callback = null);

	/**
	 * Get a list of entry names in the container.
	 */
	public function keys(): array;

	/**
	 * Add a service provider class that will add entries to the container.
	 */
	public function register(ServiceProviderInterface $provider);
}
