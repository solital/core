<?php

namespace Solital\Test\Course\Dummy\Managers;

use Solital\Core\Http\Request;
use Solital\Core\Course\Router;
use Solital\Core\Course\RouterBootManagerInterface;

class TestBootManager implements RouterBootManagerInterface
{
    protected $rewrite;

    public function __construct(array $rewrite)
    {
        $this->rewrite = $rewrite;
    }

    /**
     * Called when router loads it's routes
     *
     * @param Router $router
     * @param Request $request
     */
    public function boot(Router $router, Request $request): void
    {
        foreach ($this->rewrite as $url => $rewrite) {
            // If the current url matches the rewrite url, we use our custom route

            if ($request->getUri()->contains($url) === true) {
                $request->setRewriteUrl($rewrite);
            }

        }
    }
}