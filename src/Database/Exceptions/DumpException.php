<?php

namespace Solital\Core\Database\Exceptions;

use ModernPHPException\Interface\SolutionInterface;
use ModernPHPException\Solution;

class DumpException extends \Exception implements SolutionInterface
{
    /**
     * @return Solution
     */
    #[\Override]
    public function getSolution(): Solution
    {
        return Solution::createSolution("Verify '.env' file")
            ->setDescription('Check that you have correctly filled in the information about the database');
    }
}
