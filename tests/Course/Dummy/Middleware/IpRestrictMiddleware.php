<?php

use Solital\Core\Http\Middleware\IpRestrictAccess;

class IpRestrictMiddleware extends IpRestrictAccess {

    protected array $ip_blocklist = [
        '5.5.5.5',
        '8.8.*',
    ];

    protected array $ip_passlist = [
        '8.8.2.2',
    ];
}