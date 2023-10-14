<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Loader;

use Platine\Dev\PlatineTestCase;
use Platine\Etl\Etl;
use Platine\Etl\Iterator\TextLineIterator;
use Platine\Etl\Loader\NullLoader;

/**
 * NullLoader class tests
 *
 * @group etl
 * @group loader
 */
class NullLoaderTest extends PlatineTestCase
{
    public function testDefault(): void
    {
        $generator = new TextLineIterator("foo\n\rbar", true);
        $etl = $this->getMockInstance(Etl::class);

        $o = new NullLoader();
        $o->load($generator->getIterator(), 'a', $etl);
        $o->init();
        $o->commit(true);
        $o->rollback();
        $this->assertTrue(true);
    }
}
