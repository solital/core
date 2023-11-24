<?php

namespace Solital\Core\Cache\Exception;

use ModernPHPException\Interface\SolutionInterface;
use ModernPHPException\Solution;

class CacheAdapterException extends \RuntimeException implements SolutionInterface
{
    /**
     * @return Solution
     */
    public function getSolution(): Solution
    {
        return Solution::createSolution('Check if drive exists')
            ->setDescription('Check in php.ini if the selected driver is installed on your computer');
    }
}
