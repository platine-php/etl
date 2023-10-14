<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Iterator;

use Platine\Dev\PlatineTestCase;
use Platine\Etl\Iterator\CsvKeysAwareIterator;
use Platine\Etl\Iterator\CsvStringIterator;
use Platine\Etl\Iterator\TextLineIterator;

/**
 * CsvKeysAwareIterator class tests
 *
 * @group etl
 * @group iterator
 */
class CsvKeysAwareIteratorTest extends PlatineTestCase
{
    public function testIterateDefault(): void
    {
        $textIterator = new TextLineIterator('1,2');
        $iterator = new CsvStringIterator($textIterator);

        $o = new CsvKeysAwareIterator($iterator, ['a', 'b'], false);

        $res = $o->getIterator();

        $this->assertArrayHasKey('a', $res->current());
        $this->assertArrayHasKey('b', $res->current());
        $this->assertEquals('1', $res->current()['a']);
        $this->assertEquals('2', $res->current()['b']);
    }

    public function testIterateSkipFirstLine(): void
    {
        $textIterator = new TextLineIterator("a,b\n\r1,2");
        $iterator = new CsvStringIterator($textIterator);

        $o = new CsvKeysAwareIterator($iterator, ['a', 'b'], true);

        $res = $o->getIterator();

        $this->assertArrayHasKey('a', $res->current());
        $this->assertArrayHasKey('b', $res->current());
        $this->assertEquals('1', $res->current()['a']);
        $this->assertEquals('2', $res->current()['b']);
    }


    public function testIterateSkipFirstLineKeysEmpty(): void
    {
        $textIterator = new TextLineIterator("a,b\n\r1,2");
        $iterator = new CsvStringIterator($textIterator);

        $o = new CsvKeysAwareIterator($iterator, [], true);

        $res = $o->getIterator();

        $this->assertArrayHasKey('a', $res->current());
        $this->assertArrayHasKey('b', $res->current());
        $this->assertEquals('1', $res->current()['a']);
        $this->assertEquals('2', $res->current()['b']);
    }

    public function testIterateKeysMoreThanValues(): void
    {
        $textIterator = new TextLineIterator("a,b\n\r1,2");
        $iterator = new CsvStringIterator($textIterator);

        $o = new CsvKeysAwareIterator($iterator, ['a', 'b', 'c'], true);

        $res = $o->getIterator();

        $this->assertArrayHasKey('a', $res->current());
        $this->assertArrayHasKey('b', $res->current());
        $this->assertArrayHasKey('c', $res->current());
        $this->assertEquals('1', $res->current()['a']);
        $this->assertEquals('2', $res->current()['b']);
        $this->assertNull($res->current()['c']);
    }

    public function testIterateValuesMoreThanKeys(): void
    {
        $textIterator = new TextLineIterator("a,b,c\n\r1,2,4");
        $iterator = new CsvStringIterator($textIterator);

        $o = new CsvKeysAwareIterator($iterator, ['a', 'b'], true);

        $res = $o->getIterator();

        $this->assertCount(2, $res->current());
        $this->assertArrayHasKey('a', $res->current());
        $this->assertArrayHasKey('b', $res->current());
        $this->assertEquals('1', $res->current()['a']);
        $this->assertEquals('2', $res->current()['b']);
    }
}
