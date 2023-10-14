<?php

declare(strict_types=1);

namespace Platine\Etl\Test;

use Platine\Dev\PlatineTestCase;

/**
 * Etl class tests
 *
 * @group core
 * @group etl
 */
class EtlTest extends PlatineTestCase
{
    public function testDefault(): void
    {
        $this->assertEquals('FOO', 'FOO');
    }
}
