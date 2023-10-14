<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Iterator;

use Platine\Dev\PlatineTestCase;
use Platine\Etl\Iterator\CsvStringIterator;
use Platine\Etl\Iterator\TextLineIterator;

/**
 * CsvStringIterator class tests
 *
 * @group etl
 * @group iterator
 */
class CsvStringIteratorTest extends PlatineTestCase
{
    public function testIterateDefault(): void
    {
        $iterator = new TextLineIterator('a,b,"c d"');
        $o = new CsvStringIterator($iterator);

        $res = $o->getIterator();

        $this->assertCount(3, $res->current());
        $this->assertEquals('a', $res->current()[0]);
        $this->assertEquals('b', $res->current()[1]);
        $this->assertEquals('c d', $res->current()[2]);
    }

    public function testIterateCreateFromText(): void
    {
        $o = CsvStringIterator::createFromText('a,b,"c d"');

        $res = $o->getIterator();

        $this->assertCount(3, $res->current());
        $this->assertEquals('a', $res->current()[0]);
        $this->assertEquals('b', $res->current()[1]);
        $this->assertEquals('c d', $res->current()[2]);
    }
}
