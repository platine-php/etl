<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Transformer;

use Platine\Dev\PlatineTestCase;
use Platine\Etl\Etl;
use Platine\Etl\Transformer\CallableTransformer;

/**
 * CallableTransformer class tests
 *
 * @group etl
 * @group transform
 */
class CallableTransformerTest extends PlatineTestCase
{
    public function testTransform(): void
    {
        $o = new CallableTransformer('strtoupper');
        $etl = $this->getMockInstance(Etl::class);

        $res = $o->transform('foo', 1, $etl);

        $this->assertEquals('FOO', $res->current());
    }

    public function testTransformClosure(): void
    {
        $o = new CallableTransformer(function ($value) {
            return strlen($value);
        });
        $etl = $this->getMockInstance(Etl::class);

        $res = $o->transform('foo', 1, $etl);

        $this->assertEquals(3, $res->current());
    }
}
