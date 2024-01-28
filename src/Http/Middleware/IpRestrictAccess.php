<?php

namespace Solital\Core\Http\Middleware;

use Solital\Core\Exceptions\IpRestrictAccessException;

abstract class IpRestrictAccess implements BaseMiddlewareInterface
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
     * @throws trigger_error
     * @return void
     */
    public function handle(): void
    {
        if ($this->validate((string)request()->getIp()) === false) {
            trigger_error("Restricted ip. Access to " . request()->getIp() . " has been blocked");
            //throw new IpRestrictAccessException("Restricted ip. Access to " . request()->getIp() . " has been blocked", 403);
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
