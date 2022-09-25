<?php

namespace Solital\Core\Container\Interface;

/**
 * The interface that all service providers to the container must implement
 * in order to properly work.
 *
 * This exposes a method that we use to add entries to the container that
 * we pass in.
 */
interface ServiceProviderInterface
{
	/**
	 * Add entries to the supplied container.
	 * 
	 * @param  ContainerInterface $container The PSR-11 container.
	 * 
	 * @return void
	 */
	public function register(ContainerInterface $container);
}
