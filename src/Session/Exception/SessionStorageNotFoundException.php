<?php

namespace Solital\Core\Session\Exception;

use ModernPHPException\Interface\SolutionInterface;
use ModernPHPException\Solution;

class SessionStorageNotFoundException extends \InvalidArgumentException implements SolutionInterface
{
    public function getSolution(): Solution
    {
        return Solution::createSolution("Verify session handler")
            ->setDescription("Check the `save_path` setting in the `session.yaml` file");
    }
}
