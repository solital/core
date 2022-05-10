<?php

namespace Solital\Core\Http\Middleware;

use Solital\Core\Http\Request;

abstract class IpRestrictAccess implements MiddlewareInterface
{
    /**
     * @var array
     */
    protected array $ip_blocklist = [];

    /**
     * @var array
     */
    protected array $ip_passlist = [];

    /**
     * @param Request $request
     * @throws HttpException
     */
    public function handle(Request $request): void
    {
        if ($this->validate((string)$request->getIp()) === false) {
            throw new \Exception("Restricted ip. Access to " . $request->getIp() . " has been blocked", 403);
        }
    }

    /**
     * @param string $ip
     * 
     * @return bool
     */
    protected function validate(string $ip): bool
    {
        // Accept ip that is in pass-list
        if (in_array($ip, $this->ip_passlist, true) === true) {
            return true;
        }

        foreach ($this->ip_blocklist as $block_ip) {

            // Blocks range (8.8.*)
            if ($block_ip[strlen($block_ip) - 1] === '*' && strpos($ip, trim($block_ip, '*')) === 0) {
                return false;
            }

            // Blocks exact match
            if ($block_ip === $ip) {
                return false;
            }
        }

        return true;
    }
}
