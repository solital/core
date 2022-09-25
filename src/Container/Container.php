<?php

namespace Solital\Core\Container;

use Solital\Core\Container\Interface\{ContainerInterface, ServiceProviderInterface};
use Solital\Core\Container\Exception\{
	ExpectedInvokableException,
	NotFoundException,
	ContainerException,
	ImmutableException
};

class Container implements ContainerInterface
{
	/**
	 * Entry names only.
	 * 
	 * @var array
	 */
	private array $keys = [];

	/**
	 * All data for services and parameters.
	 * 
	 * Updated with service instances.
	 * 
	 * @var array
	 */
	private array $entries = [];

	/**
	 * Invokables that will always return a new instance.
	 * 
	 * @var mixed
	 */
	private mixed $factories = [];

	/**
	 * Invokables that should be interpreted as parameters.
	 * 
	 * @var mixed
	 */
	private mixed $protected = [];

	/**
	 * Entries currently being resolved.
	 * 
	 * @var array
	 */
	private array $resolving = [];

	/**
	 * Keys of entries that have been resolved.
	 * 
	 * @var array
	 */
	private array $resolved = [];

	/**
	 * Invokables that should be ran on every resolve.
	 * 
	 * @var array
	 */
	private array $globals = [];

	/**
	 * Create the container.
	 *
	 * Optionally pass in entries on instantiation.
	 *
	 * We use SplObjectStorage to uniquely identify the
	 * invokable definitions.
	 * 
	 * @param array $entries Service definitions and parameters.
	 */
	public function __construct(array $entries = [])
	{
		$this->factories = new \SplObjectStorage();
		$this->protected = new \SplObjectStorage();

		foreach ($entries as $id => $value) {
			$this->add($id, $value);
		}
	}

	/**
	 * Retrieve an entry.
	 *
	 * Gets a service or parameter from the container. Also
	 * calls global callbacks if there are any.
	 *
	 * @param  string $id Entry identifier.
	 * 
	 * @return mixed      Service instance or parameter value.
	 */
	public function get($id)
	{
		if (!isset($this->keys[$id])) {
			throw new NotFoundException($id);
		}

		if (isset($this->resolving[$id])) {
			throw new ContainerException(sprintf('Cyclic dependency detected while resolving "%s"', $id));
		}

		$this->resolving[$id] = true;

		$definition = $this->entries[$id];

		if (
			isset($this->resolved[$id])
			|| !$this->invokable($definition)
			|| isset($this->protected[$definition])
		) {
			$this->callGlobals($definition);
			return $definition;
		}

		$service = $definition($this);

		if (isset($this->factories[$definition])) {
			unset($this->resolving[$id]);
			$this->callGlobals($service);
			return $service;
		}

		$this->resolved[$id] = true;
		$this->entries[$id] = $service;

		unset($this->resolving[$id]);
		$this->callGlobals($service);

		return $service;
	}

	/**
	 * Check if the container contains an entry.
	 *
	 * We keep the keys array so that we can quickly
	 * lookup whether we have an entry or not.
	 * 
	 * @param  string  $id Entry identifier.
	 * 
	 * @return boolean     True if found, false otherwise.
	 */
	public function has($id): bool
	{
		return isset($this->keys[$id]);
	}

	/**
	 * Add an entry.
	 *
	 * Cannot add entries that are already resolved
	 * and shared, you must remove that entry first.
	 *
	 * We add the entry to the keys array and the
	 * entries array.
	 * 
	 * @param string $id    Entry identifier.
	 * @param mixed $value  Entry definition.
	 * 
	 * @throws ImmutableException
	 */
	public function add(string $id, $value)
	{
		if (isset($this->resolved[$id])) {
			throw new ImmutableException($id);
		}

		$this->keys[$id] = true;
		$this->entries[$id] = $value;
	}

	/**
	 * Remove an entry.
	 *
	 * If the entry that is set is an object, that means that 
	 * it is a service that is already resolved, or that it is
	 * a service definition (invokable class or closure) or
	 * protected invokable.
	 *
	 * In that case we need to remove it from the factory and
	 * protected storages.
	 * 
	 * @param string $id Entry identifier.
	 */
	public function remove(string $id)
	{
		if (isset($this->keys[$id])) {
			if (($obj = $this->entries[$id]) && is_object($obj)) {
				unset($this->factories[$obj], $this->protected[$obj]);
			}

			unset(
				$this->entries[$id],
				$this->resolving[$id],
				$this->resolved[$id],
				$this->keys[$id]
			);
		}
	}

	/**
	 * Tag a service to always return a new instance.
	 *
	 * Stops the Container::get execution short by returning the 
	 * service before adding it to the resolved array, thereby making 
	 * the entry always return a new instance of the service.
	 * 
	 * @param  mixed $callback Invokable.
	 * 
	 * @return mixed           The original invokable.
	 */
	public function factory(mixed $callback): mixed
	{
		if (!is_callable($callback)) {
			throw new ExpectedInvokableException($callback . " is not a callable type");
		}

		$this->factories->attach($callback);

		return $callback;
	}

	/**
	 * Tag an entry to be interpreted as a parameter.
	 *
	 * Since invokables are always treated as service definitions,
	 * this method exists to expose a method of treating an invokable
	 * as a literal value.
	 *
	 * This should be used in the case that you want to get the actual
	 * invokable object back rather than the object that it creates.
	 * 
	 * @param  mixed $callback Invokable.
	 * 
	 * @return mixed           The original invokable.
	 */
	public function protect(mixed $callback): mixed
	{
		if (!is_callable($callback)) {
			throw new ExpectedInvokableException($callback . " is not a callable type ");
		}

		$this->protected->attach($callback);

		return $callback;
	}

	/**
	 * Extends a service definition.
	 *
	 * Takes a callback that will be ran after a service
	 * is created.
	 *
	 * The callback will be passed the object instance and 
	 * an instance of the container. This method can be used 
	 * for setter injection.
	 *
	 * If the callback argument is left null and the id argument
	 * is an invokable, it will be treated as a global callback
	 * that should be ran on every resolve.
	 * 
	 * @param string $id       Service entry identifier.
	 * @param mixed  $callback Invokable.
	 * 
	 * @return void
	 */
	public function extend(string $id, mixed $callback = null)
	{
		if ($callback === null) {
			$callback = $id;
			$id = false;

			if (!$this->invokable($callback)) {
				throw new ExpectedInvokableException(sprintf('Invalid extend callback supplied'));
			}

			return $this->globals[] = $callback;
		}

		if (!isset($this->keys[$id])) {
			throw new NotFoundException($id);
		}

		if (isset($this->resolving[$id])) {
			throw new ImmutableException(sprintf('Cannot mutate "%s" while it\'s resolving', $id));
		}

		$definition = $this->entries[$id];

		if (!$this->invokable($definition)) {
			throw new ExpectedInvokableException(sprintf(
				'Cannot extend definition of a parameter or resolved entry'
			));
		}

		if (isset($this->protected[$definition])) {
			throw new ImmutableException(null, sprintf('Cannot extend definition of a protected entry'));
		}

		if (!$this->invokable($callback)) {
			throw new ExpectedInvokableException(sprintf('Invalid extend callback supplied'));
		}

		$callback = function ($container) use ($callback, $definition, $id) {
			return $callback($definition($container), $container);
		};

		if (isset($this->factories[$definition])) {
			$this->factories->detach($definition);
			$this->factories->attach($callback);
		}

		return $this->add($id, $callback);
	}

	/**
	 * Retrieve all entry names.
	 * 
	 * @return array Entry names.
	 */
	public function keys(): array
	{
		return array_keys($this->keys);
	}

	/**
	 * Add a service provider.
	 *
	 * A service provider is a class that provides multiple
	 * entries to the container.
	 *
	 * These are often used for organizational purposes so that
	 * you may add a series of services and/or parameters that
	 * relate to each other to a container.
	 *
	 * The provider must implement the ServiceProvider interface,
	 * which simply exposes a register method that we use to pass
	 * an instance of the container so that entries may be added.
	 * 
	 * @param  ServiceProvider $provider Service provider class.
	 * 
	 * @return ContainerInterface        The container instance.        
	 */
	public function register(ServiceProviderInterface $provider): self
	{
		$provider->register($this);

		return $this;
	}

	/**
	 * Checks whether a callback is considered
	 * invokable or not.
	 *
	 * This method is here to simply encapsulate
	 * this logic for later alteration.
	 * 
	 * @param  mixed $callback Invokable.
	 * 
	 * @return boolean         True if invokable, false otherwise.
	 */
	private function invokable($callback): bool
	{
		if (is_callable($callback)) {
			return true;
		}

		return false;
	}

	/**
	 * Calls all global callbacks.
	 *
	 * Used multiple times in the Container::get method
	 * due to the fact that the method may return a result
	 * at many different points in its execution.
	 *
	 * @param mixed $value The resolved object or value.
	 * 
	 * @return void
	 */
	private function callGlobals($value = null)
	{
		if (!empty($this->globals)) {
			foreach ($this->globals as $callback) {
				$callback($value, $this);
			}
		}
	}

	/**
	 * ArrayAccess method.
	 * 
	 * @param mixed $offset
	 * 
	 * @return bool
	 */
	public function offsetExists(mixed $offset): bool
	{
		return $this->has($offset);
	}

	/**
	 * ArrayAccess method.
	 * 
	 * @param mixed $offset
	 * 
	 * @return mixed
	 */
	public function offsetGet(mixed $offset): mixed
	{
		return $this->get($offset);
	}

	/**
	 * ArrayAccess method.
	 * 
	 * @param mixed $offset
	 * @param mixed $value
	 * 
	 * @return void
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
		$this->add($offset, $value);
	}

	/**
	 * ArrayAccess method.
	 * 
	 * @param mixed $offset
	 * 
	 * @return void
	 */
	public function offsetUnset(mixed $offset): void
	{
		$this->remove($offset);
	}
}
