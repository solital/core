<?php

namespace Solital\Core\Session\Enum;

enum SessionCacheLimiterEnum {
    case PUBLIC;
    case PRIVATE_NO_EXPIRE;
    case PRIVATE;
    case NOCACHE;
}