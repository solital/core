<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Processor;

/**
 * ProcessorAwareInterface
 *
 * @package Solital\Core\Logger
 */
interface ProcessorAwareInterface
{
    /**
     * Execute all the processors
     *
     * @return $this
     */
    public function process();

    /**
     * Set processors for THIS class
     *
     * @param  callable ...$callables
     * @return string called class name
     * @throws \LogicException if not valid processor found
     */
    public static function addProcessor(callable ...$callables): string;
}
