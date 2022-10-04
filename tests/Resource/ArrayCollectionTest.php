<?php

require_once dirname(__DIR__, 2) . "/src/Resource/Helpers/others.php";

use PHPUnit\Framework\TestCase;

class ArrayCollectionTest extends TestCase
{
    public function testAll()
    {
        $make_data = collection([1, 2, 3])->all();
        $this->assertEquals([1, 2, 3], $make_data);
    }

    public function testAvg()
    {
        $make_data = collection([['foo' => 10], ['foo' => 10], ['foo' => 20], ['foo' => 40]])->avg('foo');
        $average = collection([1, 1, 2, 4])->avg();

        $this->assertEquals(20, $make_data);
        $this->assertEquals(2, $average);
    }

    public function testChunk()
    {
        $make_data = collection([1, 2, 3, 4, 5, 6, 7]);
        $chunks = $make_data->chunk(4);

        $this->assertEquals([[1, 2, 3, 4], [4 => 5, 5 => 6, 6 => 7]], $chunks->toArray());
    }

    public function testCollapse()
    {
        $make_data = collection([[1, 2, 3], [4, 5, 6], [7, 8, 9]]);
        $collapsed = $make_data->collapse();

        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8, 9], $collapsed->all());
    }

    public function testCombine()
    {
        $make_data = collection(['name', 'age']);
        $combined = $make_data->combine(['George', 29]);

        $this->assertEquals(['name' => 'George', 'age' => 29], $combined->all());
    }

    public function testConcat()
    {
        $make_data = collection(['John Doe']);
        $concatenated = $make_data->concat(['Jane Doe'])->concat(['name' => 'Johnny Doe']);

        $this->assertEquals(['John Doe', 'Jane Doe', 'Johnny Doe'], $concatenated->all());
    }

    public function testContains()
    {
        $make_data = collection(['name' => 'Desk', 'price' => 100]);
        $make_data->contains('Desk');
        $make_data->contains('New York');

        $this->assertEquals(true, $make_data->contains('Desk'));
        $this->assertEquals(false, $make_data->contains('New York'));
    }

    public function testCount()
    {
        $make_data = collection([1, 2, 3, 4]);

        $this->assertEquals(4, $make_data->count());
    }

    public function testCountBy()
    {
        $make_data = collection([1, 2, 2, 2, 3]);
        $counted = $make_data->countBy();

        $this->assertEquals([1 => 1, 2 => 3, 3 => 1], $counted->all());
    }

    public function testDiff()
    {
        $make_data = collection([1, 2, 3, 4, 5]);
        $diff = $make_data->diff([2, 4, 6, 8]);

        $this->assertEquals([0 => 1, 2 => 3, 4 => 5], $diff->all());
    }

    public function testDiffAssoc()
    {
        $make_data = collection([
            'color' => 'orange',
            'type' => 'fruit',
            'remain' => 6
        ]);

        $diff = $make_data->diffAssoc([
            'color' => 'yellow',
            'type' => 'fruit',
            'remain' => 3,
            'used' => 6
        ]);

        $this->assertEquals(['color' => 'orange', 'remain' => 6], $diff->all());
    }

    public function testDiffKeys()
    {
        $diff = collection([
            'one' => 10,
            'two' => 20,
            'three' => 30,
            'four' => 40,
            'five' => 50,
        ]);

        $diff = $diff->diffKeys([
            'two' => 2,
            'four' => 4,
            'six' => 6,
            'eight' => 8,
        ]);

        $this->assertEquals(['one' => 10, 'three' => 30, 'five' => 50], $diff->all());
    }

    public function testExcept()
    {
        $make_data = collection(['product_id' => 1, 'price' => 100, 'discount' => false]);
        $filtered = $make_data->except(['price', 'discount']);

        $this->assertEquals(['product_id' => 1], $filtered->all());
    }

    public function testExceptMultiple()
    {
        $make_data = collection([['product_id' => 1, 'price' => 100, 'discount' => false], ['product_id' => 2, 'price' => 500, 'discount' => true]]);
        $filtered = $make_data->exceptMultiple(['price', 'discount']);

        $this->assertEquals([['product_id' => 1], ['product_id' => 2]], $filtered->all());
    }

    public function testFilter()
    {
        $make_data = collection([1, 2, 3, 4]);

        $filtered = $make_data->filter(function ($value, $key) {
            return $value > 2;
        });

        $this->assertEquals([2 => 3, 3 => 4], $filtered->all());
    }

    public function testFirst()
    {
        $make_data = collection([1, 2, 3, 4])->first(function ($value, $key) {
            return $value > 2;
        });

        $this->assertEquals(3, $make_data);
    }

    public function testFirstWhere()
    {
        $make_data = collection([
            ['name' => 'Regena', 'age' => null],
            ['name' => 'Linda', 'age' => 14],
            ['name' => 'Diego', 'age' => 23],
            ['name' => 'Linda', 'age' => 84],
        ]);

        $this->assertEquals(['name' => 'Linda', 'age' => 14], $make_data->firstWhere('name', 'Linda'));
    }

    public function testFlatMap()
    {
        $make_data = collection([
            ['name' => 'Sally'],
            ['school' => 'Arkansas'],
            ['age' => 28]
        ]);

        $flattened = $make_data->flatMap(function ($values) {
            return array_map('strtoupper', $values);
        });

        $this->assertEquals(['name' => 'SALLY', 'school' => 'ARKANSAS', 'age' => '28'], $flattened->all());
    }

    public function testFlatten()
    {
        $make_data = collection(['name' => 'jony', 'languages' => ['php', 'javascript']]);
        $flattened = $make_data->flatten();

        $this->assertEquals(['jony', 'php', 'javascript'], $flattened->all());
    }

    public function testFlip()
    {
        $make_data = collection(['name' => 'Jony', 'library' => 'array_master']);
        $flipped = $make_data->flip();

        $this->assertEquals(['Jony' => 'name', 'array_master' => 'library'], $flipped->all());
    }

    public function testForget()
    {
        $make_data = collection(['name' => 'Jony', 'library' => 'array_master']);
        $make_data->forget('name');

        $this->assertEquals(['library' => 'array_master'], $make_data->all());
    }

    public function testGet()
    {
        $make_data = collection(['name' => 'Jony', 'library' => 'array_master']);
        $value = $make_data->get('name');

        $this->assertEquals('Jony', $value);
    }

    public function testGroupBy()
    {
        $make_data = collection([
            ['account_id' => 'account-x10', 'product' => 'Chair'],
            ['account_id' => 'account-x10', 'product' => 'Bookcase'],
            ['account_id' => 'account-x11', 'product' => 'Desk'],
        ]);
        $grouped = $make_data->groupBy('account_id');

        $this->assertEquals([
            'account-x10' => [
                ['account_id' => 'account-x10', 'product' => 'Chair'],
                ['account_id' => 'account-x10', 'product' => 'Bookcase'],
            ],
            'account-x11' => [
                ['account_id' => 'account-x11', 'product' => 'Desk'],
            ],
        ], $grouped->toArray());
    }

    public function testHas()
    {
        $make_data = collection(['account_id' => 1, 'product' => 'Desk', 'amount' => 5]);

        $this->assertEquals(true, $make_data->has('product'));
    }

    public function testImplode()
    {
        $make_data = collection([
            ['account_id' => 1, 'product' => 'Desk'],
            ['account_id' => 2, 'product' => 'Chair'],
        ]);

        $this->assertEquals('Desk, Chair', $make_data->implode('product', ', '));
    }

    public function testIntersect()
    {
        $make_data = collection(['Desk', 'Sofa', 'Chair']);
        $intersect = $make_data->intersect(['Desk', 'Chair', 'Bookcase']);

        $this->assertEquals([0 => 'Desk', 2 => 'Chair'], $intersect->all());
    }

    public function testIntersectByKeys()
    {
        $make_data = collection([
            'serial' => 'UX301', 'type' => 'screen', 'year' => 2009
        ]);
        $intersect = $make_data->intersectByKeys([
            'reference' => 'UX404', 'type' => 'tab', 'year' => 2011
        ]);

        $this->assertEquals(['type' => 'screen', 'year' => 2009], $intersect->all());
    }

    public function testIsEmpty()
    {
        $make_data = collection([])->isEmpty();

        $this->assertEquals(true, $make_data);
    }

    public function testIsNotEmpty()
    {
        $make_data = collection([])->isNotEmpty();

        $this->assertEquals(false, $make_data);
    }

    public function testKeyBy()
    {
        $make_data = collection([
            ['product_id' => 'prod-100', 'name' => 'Desk'],
            ['product_id' => 'prod-200', 'name' => 'Chair'],
        ]);
        $keyed = $make_data->keyBy('product_id');

        $this->assertEquals([
            'prod-100' => ['product_id' => 'prod-100', 'name' => 'Desk'],
            'prod-200' => ['product_id' => 'prod-200', 'name' => 'Chair'],
        ], $keyed->all());
    }

    public function testKeys()
    {
        $make_data = collection([
            'prod-100' => ['product_id' => 'prod-100', 'name' => 'Desk'],
            'prod-200' => ['product_id' => 'prod-200', 'name' => 'Chair'],
        ]);
        $keys = $make_data->keys();

        $this->assertEquals(['prod-100', 'prod-200'], $keys->toArray());
    }

    public function testLast()
    {
        $make_data = collection([1, 2, 3, 4])->last();

        $this->assertEquals(4, $make_data);
    }

    public function testMap()
    {
        $make_data = collection([1, 2, 3, 4, 5]);

        $multiplied = $make_data->map(function ($item, $key) {
            return $item * 2;
        });

        $this->assertEquals([2, 4, 6, 8, 10], $multiplied->all());
    }

    public function testMapWithKeys()
    {
        $make_data = collection([
            [
                'name' => 'John',
                'department' => 'Sales',
                'email' => 'john@example.com'
            ],
            [
                'name' => 'Jane',
                'department' => 'Marketing',
                'email' => 'jane@example.com'
            ]
        ]);

        $keyed = $make_data->mapWithKeys(function ($item) {
            return [$item['email'] => $item['name']];
        });

        $this->assertEquals([
            'john@example.com' => 'John',
            'jane@example.com' => 'Jane',
        ], $keyed->all());
    }

    public function testMax()
    {
        $max1 = collection([['foo' => 10], ['foo' => 20]])->max('foo');
        $max2 = collection([['foo' => 10], ['foo' => 30]])->max('foo');

        $this->assertEquals(20, $max1);
        $this->assertEquals(30, $max2);
    }

    public function testMedian()
    {
        $median = collection([['foo' => 10], ['foo' => 10], ['foo' => 20], ['foo' => 40]])->median('foo');

        $this->assertEquals(15, $median);
    }

    public function testMerge()
    {
        $make_data = collection(['product_id' => 1, 'price' => 100]);
        $merged = $make_data->merge(['price' => 200, 'discount' => false]);

        $this->assertEquals(['product_id' => 1, 'price' => 200, 'discount' => false], $merged->all());
    }

    public function testMin()
    {
        $min1 = collection([['foo' => 10], ['foo' => 20]])->min('foo');
        $min2 = collection([1, 2, 3, 4, 5])->min();

        $this->assertEquals(10, $min1);
        $this->assertEquals(1, $min2);
    }

    public function testMode()
    {
        $mode1 = collection([['foo' => 10], ['foo' => 10], ['foo' => 20], ['foo' => 40]])->mode('foo');
        $mode2 = collection([1, 1, 2, 4])->mode();

        $this->assertEquals([10], $mode1);
        $this->assertEquals([1], $mode2);
    }

    public function testOnly()
    {
        $make_data = collection(['product_id' => 1, 'name' => 'Desk', 'price' => 100, 'discount' => false]);
        $filtered = $make_data->only(['product_id', 'name']);

        $this->assertEquals(['product_id' => 1, 'name' => 'Desk'], $filtered->all());
    }

    public function testPad()
    {
        $make_data = collection(['A', 'B', 'C']);
        $filtered = $make_data->pad(5, 0);

        $this->assertEquals(['A', 'B', 'C', 0, 0], $filtered->all());
    }

    public function testPartition()
    {
        $make_data = collection([1, 2, 3, 4, 5, 6]);

        list($underThree, $equalOrAboveThree) = $make_data->partition(function ($i) {
            return $i < 3;
        });

        $this->assertEquals([1, 2], $underThree->all());
        $this->assertEquals([2 => 3, 3 => 4, 4 => 5, 5 => 6], $equalOrAboveThree->all());
    }

    public function testPipe()
    {
        $make_data = collection([1, 2, 3]);

        $piped = $make_data->pipe(function ($make_data) {
            return $make_data->sum();
        });

        $this->assertEquals(6, $piped);
    }

    public function testPluck()
    {
        $make_data = collection([
            ['product_id' => 'prod-100', 'name' => 'Desk'],
            ['product_id' => 'prod-200', 'name' => 'Chair'],
        ]);

        $plucked = $make_data->pluck('name');

        $this->assertEquals(['Desk', 'Chair'], $plucked->all());
    }

    public function testPop()
    {
        $make_data = collection([1, 2, 3, 4, 5]);

        $this->assertEquals(5, $make_data->pop());
        $this->assertEquals([1, 2, 3, 4], $make_data->all());
    }

    public function testPrepend()
    {
        $make_data = collection([1, 2, 3, 4, 5]);
        $make_data->prepend(0);

        $this->assertEquals([0, 1, 2, 3, 4, 5], $make_data->all());
    }

    public function testPull()
    {
        $make_data = collection(['product_id' => 'prod-100', 'name' => 'Desk']);
        $make_data->pull('name');

        $this->assertEquals(['product_id' => 'prod-100'], $make_data->all());
    }

    public function testPush()
    {
        $make_data = collection([1, 2, 3, 4]);
        $make_data->push(5);

        $this->assertEquals([1, 2, 3, 4, 5], $make_data->all());
    }

    public function testPut()
    {
        $make_data = collection(['product_id' => 1, 'name' => 'Desk']);
        $make_data->put('price', 100);

        $this->assertEquals(['product_id' => 1, 'name' => 'Desk', 'price' => 100], $make_data->all());
    }

    public function testRandom()
    {
        $make_data = collection([1, 2, 3, 4, 5]);

        $this->assertIsInt($make_data->random());
    }

    public function testReduce()
    {
        $make_data = collection([1, 2, 3]);

        $total = $make_data->reduce(function ($carry, $item) {
            return $carry + $item;
        });

        $this->assertEquals(6, $total);
    }

    public function testReject()
    {
        $make_data = collection([1, 2, 3, 4]);

        $filtered = $make_data->reject(function ($value, $key) {
            return $value > 2;
        });

        $this->assertEquals([1, 2], $filtered->all());
    }

    public function testReverse()
    {
        $make_data = collection(['a', 'b', 'c', 'd', 'e']);
        $reversed = $make_data->reverse();

        $this->assertEquals([
            4 => 'e',
            3 => 'd',
            2 => 'c',
            1 => 'b',
            0 => 'a',
        ], $reversed->all());
    }

    public function testSearch()
    {
        $make_data = collection([2, 4, 6, 8]);

        $this->assertEquals(1, $make_data->search(4));
    }

    public function testShift()
    {
        $make_data = collection([1, 2, 3, 4, 5]);
        $make_data->shift();

        $this->assertEquals([2, 3, 4, 5], $make_data->all());
    }

    public function testShuffle()
    {
        $make_data = collection([1, 2, 3, 4, 5]);
        $shuffled = $make_data->shuffle();

        $this->assertIsArray($shuffled->all());
    }

    public function testSlice()
    {
        $make_data = collection([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $slice = $make_data->slice(4);

        $this->assertEquals([4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10], $slice->all());
    }

    public function testSort()
    {
        $make_data = collection([5, 3, 1, 2, 4]);
        $sorted = $make_data->sort();

        $this->assertEquals([1, 2, 3, 4, 5], $sorted->values()->all());
    }

    public function testSortBy()
    {
        $make_data = collection([
            ['name' => 'Desk', 'price' => 200],
            ['name' => 'Chair', 'price' => 100],
            ['name' => 'Bookcase', 'price' => 150],
        ]);

        $sorted = $make_data->sortBy('price');

        $this->assertEquals([
            ['name' => 'Chair', 'price' => 100],
            ['name' => 'Bookcase', 'price' => 150],
            ['name' => 'Desk', 'price' => 200],
        ], $sorted->values()->all());
    }

    public function testSortKeys()
    {
        $make_data = collection([
            'id' => 22345,
            'first' => 'John',
            'last' => 'Doe',
        ]);

        $sorted = $make_data->sortKeys();

        $sorted->all();

        $this->assertEquals([
            'first' => 'John',
            'id' => 22345,
            'last' => 'Doe',
        ], $sorted->all());
    }

    public function testSplice()
    {
        $make_data = collection([1, 2, 3, 4, 5]);
        $chunk = $make_data->splice(2);

        $this->assertEquals([3, 4, 5], $chunk->all());
    }

    public function testSplit()
    {
        $make_data = collection([1, 2, 3, 4, 5]);
        $groups = $make_data->split(3);

        $this->assertEquals([[1, 2], [3, 4], [5]], $groups->toArray());
    }

    public function testSum()
    {
        $make_data1 = collection([1, 2, 3, 4, 5])->sum();
        $make_data2 = collection([
            ['name' => 'JavaScript: The Good Parts', 'pages' => 176],
            ['name' => 'JavaScript: The Definitive Guide', 'pages' => 1096],
        ]);
        $make_data3 = collection([
            ['name' => 'Chair', 'colors' => ['Black']],
            ['name' => 'Desk', 'colors' => ['Black', 'Mahogany']],
            ['name' => 'Bookcase', 'colors' => ['Red', 'Beige', 'Brown']],
        ]);

        /* $make_data3->sum(function ($product) {
            return count($product['colors']);
        }); */

        #var_dump($make_data3);exit;

        $this->assertEquals(15, $make_data1);
        $this->assertEquals(1272, $make_data2->sum('pages'));
        #$this->assertEquals(6, $make_data3);
    }

    public function testTake()
    {
        $make_data = collection([0, 1, 2, 3, 4, 5]);
        $chunk = $make_data->take(3);

        $this->assertEquals([0, 1, 2], $chunk->all());
    }

    /* public function testTap()
    {
        $make_data = collection([2, 4, 3, 1, 5])
            ->sort()
            ->tap(function ($make_data) {
                Log::debug('Values after sorting', $make_data->values()->toArray());
            })
            ->shift();

        $this->assertEquals(1, $make_data);
    } */

    public function testTimes()
    {
        $make_data = collection()::times(10, function ($number) {
            return $number * 9;
        });

        $this->assertEquals([9, 18, 27, 36, 45, 54, 63, 72, 81, 90], $make_data->all());
    }

    public function testToJson()
    {
        $make_data = collection(['name' => 'Desk', 'price' => 200]);

        $this->assertEquals('{"name":"Desk","price":200}', $make_data->toJson());
    }

    public function testTransform()
    {
        $make_data = collection([1, 2, 3, 4, 5]);
        $make_data->transform(function ($item, $key) {
            return $item * 2;
        });

        $this->assertEquals([2, 4, 6, 8, 10], $make_data->all());
    }

    public function testUnion()
    {
        $make_data = collection([1 => ['a'], 2 => ['b']]);
        $union = $make_data->union([3 => ['c'], 1 => ['b']]);

        $this->assertEquals([1 => ['a'], 2 => ['b'], 3 => ['c']], $union->all());
    }

    public function testUnique()
    {
        $make_data = collection([1, 1, 2, 2, 3, 4, 2]);
        $unique1 = $make_data->unique();

        $make_data = collection([
            ['name' => 'iPhone 6', 'brand' => 'Apple', 'type' => 'phone'],
            ['name' => 'iPhone 5', 'brand' => 'Apple', 'type' => 'phone'],
            ['name' => 'Apple Watch', 'brand' => 'Apple', 'type' => 'watch'],
            ['name' => 'Galaxy S6', 'brand' => 'Samsung', 'type' => 'phone'],
            ['name' => 'Galaxy Gear', 'brand' => 'Samsung', 'type' => 'watch'],
        ]);

        $unique2 = $make_data->unique('brand');

        $this->assertEquals([1, 2, 3, 4], $unique1->values()->all());
        $this->assertEquals(
            [
                ['name' => 'iPhone 6', 'brand' => 'Apple', 'type' => 'phone'],
                ['name' => 'Galaxy S6', 'brand' => 'Samsung', 'type' => 'phone'],
            ],
            $unique2->values()->all()
        );
    }

    public function testUnless()
    {
        $make_data = collection([1, 2, 3]);

        $make_data->unless(true, function ($make_data) {
            return $make_data->push(4);
        });

        $make_data->unless(false, function ($make_data) {
            return $make_data->push(5);
        });

        $this->assertEquals([1, 2, 3, 5], $make_data->all());
    }

    public function testValues()
    {
        $make_data = collection([
            10 => ['product' => 'Desk', 'price' => 200],
            11 => ['product' => 'Desk', 'price' => 200]
        ]);

        $values = $make_data->values();

        $this->assertEquals([
            0 => ['product' => 'Desk', 'price' => 200],
            1 => ['product' => 'Desk', 'price' => 200],
        ], $values->all());
    }

    public function testWhen()
    {
        $make_data = collection([1, 2, 3]);

        $make_data->when(true, function ($make_data) {
            return $make_data->push(4);
        });

        $make_data->when(false, function ($make_data) {
            return $make_data->push(5);
        });

        $this->assertEquals([1, 2, 3, 4], $make_data->all());
    }

    public function testWhenEmpty()
    {
        $make_data = collection(['michael', 'tom']);

        $make_data->whenEmpty(function ($make_data) {
            return $make_data->push('adam');
        });


        $this->assertEquals(['michael', 'tom'], $make_data->all());
    }

    public function testWhenNotEmpty()
    {
        $make_data = collection(['michael', 'tom']);

        $make_data->whenNotEmpty(function ($make_data) {
            return $make_data->push('adam');
        });

        $this->assertEquals(['michael', 'tom', 'adam'], $make_data->all());
    }

    public function testWhere()
    {
        $make_data = collection([
            ['product' => 'Desk', 'price' => 200],
            ['product' => 'Chair', 'price' => 100],
            ['product' => 'Bookcase', 'price' => 150],
            ['product' => 'Door', 'price' => 100],
        ]);

        $filtered = $make_data->where('price', 100);

        $this->assertEquals([
            1 => ['product' => 'Chair', 'price' => 100],
            3 => ['product' => 'Door', 'price' => 100],
        ], $filtered->all());
    }

    public function testWhereBetween()
    {
        $make_data = collection([
            ['product' => 'Desk', 'price' => 200],
            ['product' => 'Chair', 'price' => 80],
            ['product' => 'Bookcase', 'price' => 150],
            ['product' => 'Pencil', 'price' => 30],
            ['product' => 'Door', 'price' => 100],
        ]);

        $filtered = $make_data->whereBetween('price', [100, 200]);

        $this->assertEquals([
            0 => ['product' => 'Desk', 'price' => 200],
            2 => ['product' => 'Bookcase', 'price' => 150],
            4 => ['product' => 'Door', 'price' => 100],
        ], $filtered->all());
    }

    public function testWhereIn()
    {
        $make_data = collection([
            ['product' => 'Desk', 'price' => 200],
            ['product' => 'Chair', 'price' => 100],
            ['product' => 'Bookcase', 'price' => 150],
            ['product' => 'Door', 'price' => 100],
        ]);

        $filtered = $make_data->whereIn('price', [150, 200]);

        $this->assertEquals([
            2 => ['product' => 'Bookcase', 'price' => 150],
            0 => ['product' => 'Desk', 'price' => 200],
        ], $filtered->all());
    }

    public function testWhereNotBetween()
    {
        $make_data = collection([
            ['product' => 'Desk', 'price' => 200],
            ['product' => 'Chair', 'price' => 80],
            ['product' => 'Bookcase', 'price' => 150],
            ['product' => 'Pencil', 'price' => 30],
            ['product' => 'Door', 'price' => 100],
        ]);

        $filtered = $make_data->whereNotBetween('price', [100, 200]);

        $this->assertEquals([
            1 => ['product' => 'Chair', 'price' => 80],
            3 => ['product' => 'Pencil', 'price' => 30],
        ], $filtered->all());
    }

    public function testWhereNotIn()
    {
        $make_data = collection([
            ['product' => 'Desk', 'price' => 200],
            ['product' => 'Chair', 'price' => 100],
            ['product' => 'Bookcase', 'price' => 150],
            ['product' => 'Door', 'price' => 100],
        ]);

        $filtered = $make_data->whereNotIn('price', [150, 200]);

        $this->assertNotEquals(['product_id' => 1, 'name' => 'Desk'], $filtered->all());
    }

    public function testWrap()
    {
        $make_data = collection()::wrap('John Doe');

        $this->assertEquals(['John Doe'], $make_data->all());
    }

    public function testZip()
    {
        $make_data = collection(['Chair', 'Desk']);
        $zipped = $make_data->zip([100, 200]);

        $this->assertEquals([[0 => 'Chair', 1 => 100], [0 => 'Desk', 1 => 200]], $zipped->toArray());
    }
}
