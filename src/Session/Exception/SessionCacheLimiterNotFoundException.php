<?php

namespace Solital\Core\Session\Exception;

use ModernPHPException\Interface\SolutionInterface;
use ModernPHPException\Solution;

class SessionCacheLimiterNotFoundException extends \InvalidArgumentException implements SolutionInterface
{
    public function getSolution(): Solution
    {
        return Solution::createSolution("Verify session cache limiter")
            ->setDescription("Check the `cache_limiter` setting in the `session.yaml` file");
    }
}