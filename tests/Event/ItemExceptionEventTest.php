<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Event;

use Exception;
use Platine\Dev\PlatineTestCase;
use Platine\Etl\Etl;
use Platine\Etl\Event\ItemExceptionEvent;

/**
 * ItemExceptionEvent class tests
 *
 * @group etl
 * @group event
 */
class ItemExceptionEventTest extends PlatineTestCase
{
    public function testDefault(): void
    {
        $etl = $this->getMockInstance(Etl::class);
        $e = new Exception();
        $o = new ItemExceptionEvent('demo', 'foo', 'key', $etl, $e);
        $this->assertInstanceOf(ItemExceptionEvent::class, $o);
        $this->assertEquals('demo', $o->getName());
        $this->assertEquals('foo', $o->getItem());
        $this->assertEquals('key', $o->getKey());
        $this->assertEquals($e, $o->getException());
        $this->assertTrue($o->shouldThrowException());
        $o->ignoreException();
        $this->assertFalse($o->shouldThrowException());
    }
}
