<?php

# MAIN DATABASE
define('DB_CONFIG', [
    'DRIVE' => 'mysql',
    'HOST' => 'localhost',
    'DBNAME' => 'test',
    'USER' => 'root',
    'PASS' => ''
]);

define('DB_CACHE', [
    'CACHE_TYPE' => 'memcached',
    'CACHE_HOST' => '127.0.0.1',
    'CACHE_PORT' => 11211,
    'CACHE_TTL' => 600
]);

# SECOND DATABASE
define('DB_CONFIG_SECONDARY', [
    'HOST' => 'localhost',
    'DBNAME' => 'test',
    'USER' => 'root',
    'PASS' => ''
]);

define('MAIL_HOST', '');
define('MAIL_USER', '');
define('MAIL_PASS', '');
define('MAIL_SECURE', 'tls');
define('MAIL_PORT', '587');