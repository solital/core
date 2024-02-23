<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use PHPUnit\Framework\TestCase;
use Solital\Core\Cache\Psr6\CachePool;
use Solital\Core\Cache\SimpleCache;

class SimpleCacheTest extends TestCase
{
    public function create()
    {
        return new SimpleCache();
    }

    public function testSimpleCache()
    {
        $cache = cache();

        $cache->delete('simple');

        assert($cache->has('simple') === false);
        assert($cache->get('simple') === null);

        $cache->set('simple', 123);

        assert($cache->has('simple') === true);
        assert($cache->get('simple') === 123);

        $cache->delete('simple');

        assert($cache->has('simple') === false);
        assert($cache->get('simple') === null);
        
        $this->assertTrue(true);
    }

    public function testMultipleCache()
    {
        $cache = $this->create();
        $cache->setMultiple([
            [
                'name' => 'brenno',
                'email' => 'email@gmail.com'
            ],
            [
                'name' => 'test',
                'email' => 'second_email@gmail.com'
            ]
        ], 100);

        $cache->getMultiple([0, 1]);  
        $cache->deleteMultiple([0, 1]);

        $this->assertTrue(true);
    }
}
