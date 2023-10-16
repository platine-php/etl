<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Event;

use Platine\Dev\PlatineTestCase;
use Platine\Etl\Etl;
use Platine\Etl\Event\ItemEvent;

/**
 * ItemEvent class tests
 *
 * @group etl
 * @group event
 */
class ItemEventTest extends PlatineTestCase
{
    public function testDefault(): void
    {
        $etl = $this->getMockInstance(Etl::class);

        $o = new ItemEvent('demo', 'foo', 'key', $etl);
        $this->assertInstanceOf(ItemEvent::class, $o);
        $this->assertEquals('demo', $o->getName());
        $this->assertEquals('foo', $o->getItem());
        $this->assertEquals('key', $o->getKey());
    }
}
