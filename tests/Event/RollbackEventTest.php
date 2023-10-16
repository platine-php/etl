<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Event;

use Platine\Dev\PlatineTestCase;
use Platine\Etl\Etl;
use Platine\Etl\Event\RollbackEvent;

/**
 * RollbackEvent class tests
 *
 * @group etl
 * @group event
 */
class RollbackEventTest extends PlatineTestCase
{
    public function testDefault(): void
    {
        $etl = $this->getMockInstance(Etl::class);

        $o = new RollbackEvent($etl, 5);
        $this->assertInstanceOf(RollbackEvent::class, $o);
        $this->assertEquals(5, $o->getCounter());
    }
}
