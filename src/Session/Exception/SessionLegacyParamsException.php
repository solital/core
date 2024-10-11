<?php

namespace Solital\Core\Session\Exception;

use ModernPHPException\Interface\SolutionInterface;
use ModernPHPException\Solution;

class SessionLegacyParamsException extends \InvalidArgumentException implements SolutionInterface
{
    public function getSolution(): Solution
    {
        return Solution::createSolution("Verify `Session` documentation")
            ->setDocs("https://solital.github.io/site/docs/4.x/session/");
    }
}