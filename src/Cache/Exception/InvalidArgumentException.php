<?php

namespace Solital\Core\Cache\Exception;

use Psr\Cache\InvalidArgumentException as PsrInvalidArgumentException;
use InvalidArgumentException as SplInvalidArgumentException;
use ModernPHPException\Interface\SolutionInterface;
use ModernPHPException\Solution;

class InvalidArgumentException extends SplInvalidArgumentException implements PsrInvalidArgumentException, SolutionInterface
{
    /**
     * @return Solution
     */
    public function getSolution(): Solution
    {
        return Solution::createSolution('Verify $key value')
            ->setDescription('The value of the $key variable is probably not of type string');
    }
}
