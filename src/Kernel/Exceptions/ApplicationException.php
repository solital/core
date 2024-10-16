<?php

namespace Solital\Core\Kernel\Exceptions;

use ModernPHPException\Interface\SolutionInterface;
use ModernPHPException\Solution;

class ApplicationException extends \Exception implements SolutionInterface
{
    public function getSolution(): Solution
    {
        return Solution::createSolution('Set the SITE_ROOT constant')
            ->setDescription('Set the SITE_ROOT constant in the `config.php` file');
    }
}
