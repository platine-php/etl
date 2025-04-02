<?php

declare(strict_types=1);

namespace Platine\Etl\Test;

use InvalidArgumentException;
use Platine\Dev\PlatineTestCase;
use Platine\Etl\Etl;
use Platine\Etl\EtlTool;
use Platine\Etl\Extractor\CsvExtractor;
use Platine\Etl\Loader\NullLoader;
use Platine\Etl\Transformer\CallableTransformer;
use Platine\Event\Dispatcher;
use RuntimeException;

/**
 * EtlTool class tests
 *
 * @group core
 * @group etl
 */
class EtlToolTest extends PlatineTestCase
{
    public function testConstructDefault(): void
    {
        $extractor = $this->getMockInstance(CsvExtractor::class);
        $transformer = $this->getMockInstance(CallableTransformer::class);
        $loader = $this->getMockInstance(NullLoader::class);

        $o = new EtlTool($extractor, $transformer, $loader);

        $this->assertInstanceOf(EtlTool::class, $o);
        $this->assertIsCallable($this->getPropertyValue(EtlTool::class, $o, 'extractor'));
        $this->assertIsCallable($this->getPropertyValue(EtlTool::class, $o, 'transformer'));
        $this->assertIsCallable($this->getPropertyValue(EtlTool::class, $o, 'loader'));
    }

    public function testExtractor(): void
    {
        $o = new EtlTool();

        $this->assertInstanceOf(EtlTool::class, $o);
        $this->assertNull($this->getPropertyValue(EtlTool::class, $o, 'extractor'));

        $o->extractor(fn() => true);
        $this->assertIsCallable($this->getPropertyValue(EtlTool::class, $o, 'extractor'));
    }

    public function testTransformer(): void
    {
        $o = new EtlTool();

        $this->assertInstanceOf(EtlTool::class, $o);
        $this->assertNull($this->getPropertyValue(EtlTool::class, $o, 'transformer'));

        $o->transformer(fn() => true);
        $this->assertIsCallable($this->getPropertyValue(EtlTool::class, $o, 'transformer'));
    }

    public function testLoader(): void
    {
        $o = new EtlTool();

        $this->assertInstanceOf(EtlTool::class, $o);
        $this->assertNull($this->getPropertyValue(EtlTool::class, $o, 'loader'));

        $o->loader(fn() => true);
        $this->assertIsCallable($this->getPropertyValue(EtlTool::class, $o, 'loader'));
    }

    public function testCreateInvalidLoader(): void
    {
        $o = new EtlTool();

        $this->expectException(RuntimeException::class);
        $o->create();
    }

    public function testCreateInvalidFlushCount(): void
    {
        $o = new EtlTool();
        $o->setFlushCount(-1);
        $o->loader(fn() => true);
        $this->expectException(RuntimeException::class);
        $o->create();
    }

    public function testCreateSuccess(): void
    {
        $o = new EtlTool();
        $o->loader(fn() => true);

        $etl = $o->create();

        $this->assertInstanceOf(Etl::class, $etl);
    }

    public function testEvents(): void
    {
        $dispatcher = $this->getMockInstance(Dispatcher::class);

        $dispatcher->expects($this->exactly(13))
                ->method('addListener');


        $o = new EtlTool(null, null, null, $dispatcher);

        $o->onStart(fn() => true);
        $o->onExtract(fn() => true);
        $o->onTransform(fn() => true);
        $o->onExtractException(fn() => true);
        $o->onTransformException(fn() => true);
        $o->onLoaderInit(fn() => true);
        $o->onLoad(fn() => true);
        $o->onLoadException(fn() => true);
        $o->onFlush(fn() => true);
        $o->onSkip(fn() => true);
        $o->onStop(fn() => true);
        $o->onRollback(fn() => true);
        $o->onEnd(fn() => true);
    }
}
