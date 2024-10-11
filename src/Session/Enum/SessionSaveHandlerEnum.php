<?php

namespace Solital\Core\Session\Enum;

enum SessionSaveHandlerEnum
{
    case FILES;
    case MEMCACHED;
    case SQLITE;
    case REDIS;
}
