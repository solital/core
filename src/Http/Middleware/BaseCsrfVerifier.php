<?php

namespace Solital\Core\Http\Middleware;

use Solital\Core\Exceptions\InvalidArgumentException;
use Solital\Core\Http\{Request, Security\SessionTokenProvider, Security\TokenProviderInterface};

class BaseCsrfVerifier
{
    public const POST_KEY = 'csrf_token';
    public const HEADER_KEY = 'X-CSRF-TOKEN';

    /**
     * @var array
     */
    protected array $except = [];

    /**
     * @var SessionTokenProvider
     */
    protected SessionTokenProvider $tokenProvider;

    /**
     * BaseCsrfVerifier constructor.
     * 
     * @throws \Solital\Http\Security\Exceptions\SecurityException
     */
    public function __construct()
    {
        $this->tokenProvider = new SessionTokenProvider();
    }

    /**
     * Check if the url matches the urls in the except property
     * 
     * @param Request $request
     * 
     * @return bool
     */
    protected function skip(Request $request): bool
    {
        if ($this->except === null || \count($this->except) === 0) {
            return false;
        }

        $max = \count($this->except) - 1;

        for ($i = $max; $i >= 0; $i--) {
            $url = $this->except[$i];
            //$url = rtrim($url, '/');

            if ($url[\strlen($url) - 1] === '*') {
                $url = rtrim($url, '*');
                $skip = $request->getUri()->contains($url);
            } else {
                $skip = ($url == $request->getUri()->getOriginalUrl());
            }

            if ($skip === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle request
     *
     * @param Request $request
     * 
     * @return void
     * @throws InvalidArgumentException
     */
    public function validateToken(Request $request): void
    {
        if ($this->skip($request) === false && \in_array($request->getMethod(), ['post', 'put', 'delete'], true) === true) {

            if ($this->tokenProvider->validate() === false) {
                throw new InvalidArgumentException("Invalid CSRF-token");
            }
        }
    }

    /**
     * Get token provider
     * 
     * @return TokenProviderInterface
     */
    public function getTokenProvider(): TokenProviderInterface
    {
        return $this->tokenProvider;
    }

    /**
     * Set token provider
     * 
     * @param TokenProviderInterface $provider
     * 
     * @return TokenProviderInterface
     */
    public function setTokenProvider(TokenProviderInterface $provider): void
    {
        $this->tokenProvider = $provider;
    }
}
