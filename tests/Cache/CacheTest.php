<?php

use Solital\Core\Cache\Cache;
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{
    public function testCache()
    {
        $cache = new Cache();

        $list = [
            'name' => 'Lorem Ipsum',
            'email' => 'solital@email.com'
        ];

        if ($cache->has('list') == true) {
            $res = $cache->get('list');
            $this->assertIsArray($res);
        } else {
            $res = $cache->set('list', $list, 20);
            $this->assertTrue($res);
        }
    }

    public function testMultipleCache()
    {
        $cache = new Cache();

        $list = [
            'nome' => 'Harvey Specter',
            'email' => 'specter@pearsonhardman.com'
        ];

        $list2 = [
            'nome' => 'Louis Litt',
            'email' => 'liitup@pearsonhardman.com'
        ];

        $return = $cache->getMultiple(['list1', 'list2']);

        if (!empty($return)) {
            $this->assertIsArray($return);
        } else {
            $res = $cache->setMultiple([
                'list1' => $list,
                'list2' => $list2
            ], 20);
    
            $this->assertTrue($res);
        }
    }
}
