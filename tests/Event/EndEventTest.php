<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Event;

use Platine\Dev\PlatineTestCase;
use Platine\Etl\Etl;
use Platine\Etl\Event\EndEvent;

/**
 * EndEvent class tests
 *
 * @group etl
 * @group event
 */
class EndEventTest extends PlatineTestCase
{
    public function testDefault(): void
    {
        $etl = $this->getMockInstance(Etl::class);

        $o = new EndEvent($etl, 5);
        $this->assertInstanceOf(EndEvent::class, $o);
        $this->assertEquals(5, $o->getCounter());
    }
}
