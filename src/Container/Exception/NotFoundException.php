<?php

namespace Solital\Core\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Thrown when an entry is not found in the container.
 *
 * This can be avoided by using the PSR-11 Container::has method.
 */
class NotFoundException extends \InvalidArgumentException implements NotFoundExceptionInterface
{
	/**
	 * Construct an exception message.
	 * 
	 * @param string $id Entry identifier.
	 */
	public function __construct($id)
	{
		parent::__construct(sprintf('Container entry "%s" not defined', $id));
	}
}
