<?php

namespace Solital\Core\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

/**
 * Thrown when attempting to alter a definition when it cannot be altered.
 *
 * Times that this can be thrown, for example, are when an object is currently 
 * resolving. The container won't be able to alter a definition while it is 
 * currently resolving that entry.
 */
class ImmutableException extends \Exception implements ContainerExceptionInterface
{
	/**
	 * Construct a message to be sent to the parent.
	 * 
	 * @param string $id      Entry identifier.
	 * @param string $message Optional custom message.
	 */
	public function __construct(?string $id, ?string $message = null)
	{
		if ($message) {
			parent::__construct($message);
		}

		parent::__construct('Cannot mutate "' . $id . '" after it has been resolved or is resolving');
	}
}
