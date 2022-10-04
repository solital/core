<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Processor;

class MemoryProcessor extends ProcessorAbstract
{
    /**
     * @param array $context
     * 
     * @return array
     */
    protected function updateContext(array $context): array
    {
        $context['memory_used'] = number_format(memory_get_usage(TRUE) / 1048575, 2);
        $context['memory_peak'] = number_format(memory_get_peak_usage(TRUE) / 1048575, 2);

        return $context;
    }
}
