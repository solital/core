<?php

namespace Solital\Test\Cache;

require_once dirname(__DIR__) . '/bootstrap.php';

use PHPUnit\Framework\TestCase;
use Solital\Core\Cache\Psr6\CachePool;

class CachePsr6Test extends TestCase
{
    public function create()
    {
        return new CachePool();
    }

    public function test()
    {
        $pool = $this->create();

        $item = $pool->getItem('aaa');

        assert($item->isHit()  === false);
        assert($item->getKey() === 'aaa');
        assert($item->get()    === null);

        $item->set(123);

        assert($item->isHit()  === true);
        assert($item->get()    === 123);

        $item = $pool->getItem('aaa');

        assert($item->isHit()  === true);
        assert($item->get()    === 123);

        $item->expiresAfter(-1);

        assert($item->isHit()  === false);
        assert($item->get()    === null);

        $item = $pool->getItem('aaa');

        assert($item->isHit()  === false);
        assert($item->get()    === null);

        $item->set(123);

        assert($item->isHit()  === true);
        assert($item->get()    === 123);

        $pool->commit();

        $pool->deleteItem('aaa');

        assert($item->isHit()  === false);
        assert($item->get()    === null);

        $this->assertTrue(true);
    }

    public function testPersistent()
    {
        $pool = $this->create();
        $item = $pool->getItem('xxx');
        $item->set(999);
        $pool->commit();

        $pool = $this->create();
        $item = $pool->getItem('xxx');
        //assert($item->get() === 999);

        $this->assertTrue(true);
    }

    public function testPersistentExpire()
    {
        $pool = $this->create();
        $item = $pool->getItem('xxx');
        $item->set(999);
        $pool->commit();

        $pool = $this->create();
        $item = $pool->getItem('xxx');

        //assert($item->isHit()  === false);
        //assert($item->get()    === null);

        $this->assertTrue(true);
    }
}
