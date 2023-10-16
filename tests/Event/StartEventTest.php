<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Event;

use Platine\Dev\PlatineTestCase;
use Platine\Etl\Etl;
use Platine\Etl\Event\BaseEvent;
use Platine\Etl\Event\StartEvent;

/**
 * StartEvent class tests
 *
 * @group etl
 * @group event
 */
class StartEventTest extends PlatineTestCase
{
    public function testDefault(): void
    {
        $etl = $this->getMockInstance(Etl::class);

        $o = new StartEvent($etl);
        $this->assertInstanceOf(StartEvent::class, $o);
        $this->assertEquals($etl, $o->getEtl());
        $this->assertEquals(BaseEvent::START, $o->getName());
    }
}
