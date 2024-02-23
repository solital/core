<?php

namespace Solital\Core\Kernel\Exceptions;

use ModernPHPException\Interface\SolutionInterface;
use ModernPHPException\Solution;

class YamlException extends \Exception implements SolutionInterface
{
    public function getSolution(): Solution
    {
        return Solution::createSolution("Run 'php vinci generate:files' command")
            ->setDescription('This command will copy all necessary configuration files');
    }
}
