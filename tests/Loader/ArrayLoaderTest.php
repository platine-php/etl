<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Loader;

use Platine\Dev\PlatineTestCase;
use Platine\Etl\Etl;
use Platine\Etl\Iterator\TextLineIterator;
use Platine\Etl\Loader\ArrayLoader;

/**
 * ArrayLoader class tests
 *
 * @group etl
 * @group loader
 */
class ArrayLoaderTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        $data = ['a', 'b'];
        $o = new ArrayLoader(true, $data);

        $this->assertCount(2, $o->getData());
    }

    public function testLoadPreseverKeys(): void
    {
        $generator = new TextLineIterator("foo\n\rbar", true);
        $etl = $this->getMockInstance(Etl::class);
        $data = [];
        $o = new ArrayLoader(true, $data);

        $o->load($generator->getIterator(), 'a', $etl);

        $this->assertCount(1, $o->getData());
        $this->assertArrayHasKey('a', $o->getData());
        $this->assertEquals('bar', $o->getData()['a']);
    }

    public function testLoadPreseverKeysUsingOptions(): void
    {
        $generator = new TextLineIterator("foo\n\rbar", true);
        $etl = $this->getMockInstance(Etl::class);
        $data = [];
        $o = new ArrayLoader(false, $data);

        $o->init(['preserve_keys' => true]);

        $o->load($generator->getIterator(), 'a', $etl);

        $this->assertCount(1, $o->getData());
        $this->assertArrayHasKey('a', $o->getData());
        $this->assertEquals('bar', $o->getData()['a']);
    }

    public function testLoadIgnorePreseverKeys(): void
    {
        $generator = new TextLineIterator("foo\n\rbar", true);
        $etl = $this->getMockInstance(Etl::class);
        $data = [];
        $o = new ArrayLoader(false, $data);

        $o->load($generator->getIterator(), 'a', $etl);

        $this->assertCount(2, $o->getData());
        $this->assertEquals('foo', $o->getData()[0]);
        $this->assertEquals('bar', $o->getData()[1]);
    }

    public function testEmptyMethod(): void
    {
        $data = ['a', 'b'];
        $o = new ArrayLoader(true, $data);
        $o->init();
        $o->commit(true);
        $o->rollback();
        $this->assertTrue(true);
    }
}
