<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Iterator;

use Platine\Dev\PlatineTestCase;
use Platine\Etl\Iterator\TextLineIterator;

/**
 * TextLineIterator class tests
 *
 * @group etl
 * @group iterator
 */
class TextLineIteratorTest extends PlatineTestCase
{
    public function testIterateSkipEmptyLine(): void
    {
        $o = new TextLineIterator("foo\n\rbar", true);

        $res = $o->getIterator();

        $this->assertEquals('foo', $res->current());
    }

    public function testIterateDontSkipEmptyLine(): void
    {
        $o = new TextLineIterator("foo\n\rbar", false);

        $res = $o->getIterator();

        $this->assertEquals('foo', $res->current());
    }
}
