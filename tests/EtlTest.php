<?php

declare(strict_types=1);

namespace Platine\Etl\Test;

use Error;
use Exception;
use Generator;
use Platine\Dev\PlatineTestCase;
use Platine\Etl\Etl;
use Platine\Etl\EtlTool;
use Platine\Etl\Event\ItemEvent;
use Platine\Etl\Event\ItemExceptionEvent;
use Platine\Etl\Exception\EtlException;
use Platine\Etl\Loader\NullLoader;

/**
 * Etl class tests
 *
 * @group core
 * @group etl
 */
class EtlTest extends PlatineTestCase
{
    public function testCreate(): void
    {
        $o = new Etl();
        $this->assertInstanceOf(Etl::class, $o);
    }

    public function testCreateWithTool(): void
    {
        $tool = new EtlTool();
        $tool->extractor(fn() => true);
        $tool->transformer(fn() => true);
        $tool->loader(fn() => true);

        $o = $tool->create();
        $this->assertInstanceOf(Etl::class, $o);
    }

    public function testProcess(): void
    {
        $target = [];

        $tool = new EtlTool();
        $tool->extractor(fn($input, Etl $etl) => ['a', 'b']);
        $tool->transformer(fn() => yield ['a', 'b']);
        $tool->loader(function ($item, $key, $etl) use (&$target) {
            $target = $item;
        });

        $o = $tool->create();
        $o->process('a,b');
        $this->assertEquals(['a','b'], $target->current());
    }

    public function testProcessUsingOptions(): void
    {
        $target = [];

        $tool = new EtlTool();
        $tool->extractor(fn($input, Etl $etl) => ['a', 'b']);
        $tool->transformer(fn() => yield ['a', 'b']);
        $tool->loader(function ($item, $key, Etl $etl) use (&$target) {
            $target = $item;
        });

        $o = $tool->create();
        $this->assertCount(0, $o->getOptions());
        $o->process('a,b', ['foo' => 123]);
        $this->assertEquals(['a','b'], $target->current());
        $this->assertCount(1, $o->getOptions());
        $this->assertArrayHasKey('foo', $o->getOptions());
        $this->assertEquals(123, $o->getOptions()['foo']);
        $o->setOptions(['foo' => 122]);
        $this->assertCount(1, $o->getOptions());
        $this->assertArrayHasKey('foo', $o->getOptions());
        $this->assertEquals(122, $o->getOptions()['foo']);
    }

    public function testProcessNullLoader(): void
    {
        $target = [];

        $tool = new EtlTool();
        $tool->extractor(fn($input, Etl $etl) => ['a', 'b']);
        $tool->transformer(fn() => yield ['a', 'b']);
        $tool->loader(new NullLoader());

        $o = $tool->create();
        $o->process('a,b');
        $this->assertCount(0, $target);
    }

    public function testProcessSkipCurrentItem(): void
    {
        $target = [];

        $tool = new EtlTool();
        $tool->extractor(function ($input, Etl $etl) {
            $etl->triggerFlush();
            return ['a', 'b', 'c'];
        });
        $tool->transformer(function ($value, $key, Etl $etl) {
            if ($value === 'b') {
                $etl->skipCurrentItem();
            }
            yield $value;
        });
        $tool->loader(function (Generator $item, $key, Etl $etl) use (&$target) {
            $target[] = $item;
        });

        $o = $tool->create();
        $o->process();

        $this->assertEquals(2, count($target));
        $this->assertEquals('a', $target[0]->current());
        $this->assertEquals('c', $target[1]->current());
    }

    public function testProcessStop(): void
    {
        $target = [];

        $tool = new EtlTool();
        $tool->extractor(fn($input, Etl $etl) => ['a', 'b', 'c']);
        $tool->transformer(function ($value, $key, Etl $etl) {
            if ($value === 'b') {
                $etl->stopProcess(true);
            }
            yield $value;
        });
        $tool->loader(function (Generator $item, $key, Etl $etl) use (&$target) {
            $etl->triggerFlush();
            $target[] = $item;
        });

        $o = $tool->create();
        $o->process();

        $this->assertEquals(1, count($target));
        $this->assertEquals('a', $target[0]->current());
    }

    public function testProcessListenerThrowException(): void
    {
        $target = [];

        $tool = new EtlTool();
        $tool->extractor(fn($input, Etl $etl) => ['a', 'b', 'c']);
        $tool->transformer(function ($value, $key, Etl $etl) {
            if ($value === 'b') {
                $etl->stopProcess(true);
            }
            yield $value;
        });
        $tool->loader(function (Generator $item, $key, Etl $etl) use (&$target) {
            $etl->triggerFlush();
            $target[] = $item;
        });

        $tool->onTransform(function () {
            throw new EtlException();
        });
        $tool->onExtract(function (ItemEvent $e) {
            throw new EtlException();
        });

        $o = $tool->create();
        $o->process();

        $this->assertEquals(0, count($target));
    }

    public function testExtractDataReturnNull(): void
    {
        $target = [];

        $tool = new EtlTool();
        $tool->extractor(function ($input, Etl $etl) {
            $etl->triggerFlush();
            $etl->stopProcess(true);
            return null;
        });
        $tool->loader(new NullLoader());

        $o = $tool->create();
        $o->process();

        $this->assertEquals(0, count($target));
    }

    public function testExtractDataReturnNotIterable(): void
    {
        $target = [];

        $tool = new EtlTool();
        $tool->extractor(fn($input, Etl $etl) => 23);
        $tool->loader(function (Generator $item, $key, Etl $etl) use (&$target) {
            $target[] = $item;
        });

        $this->expectException(EtlException::class);
        $o = $tool->create();
        $o->process();
    }

    public function testTransformReturnNotGenerator(): void
    {
        $target = [];

        $tool = new EtlTool();
        $tool->extractor(fn($input, Etl $etl) => ['a', 'b', 'c']);
        $tool->transformer(function ($value, $key, Etl $etl) {
            return $value;
        });
        $tool->loader(function (Generator $item, $key, Etl $etl) use (&$target) {
            $target[] = $item;
        });

        $this->expectException(EtlException::class);
        $o = $tool->create();
        $o->process();
    }

    public function testLoadDataError(): void
    {
        $target = [];

        $tool = new EtlTool();
        $tool->extractor(function ($input, Etl $etl) {
            return ['a', 'b', 'c'];
        });
        $tool->loader(function (Generator $item, $key, Etl $etl) use (&$target) {
            throw new EtlException();
        });

        $this->expectException(EtlException::class);
        $o = $tool->create();
        $o->process();
        $this->assertEquals(0, count($target));
    }

    public function testLoadDataErrorIgnoreException(): void
    {
        $target = [];

        $tool = new EtlTool();
        $tool->onLoadException(function (ItemExceptionEvent $e) {
            $e->ignoreException();
        });
        $tool->extractor(function ($input, Etl $etl) {
            return ['a', 'b', 'c'];
        });
        $tool->loader(function (Generator $item, $key, Etl $etl) use (&$target) {
            throw new EtlException();
        });

        $o = $tool->create();
        $o->process();
        $this->assertEquals(0, count($target));
    }

    public function testProcessSkip(): void
    {
        $target = [];

        $tool = new EtlTool();
        $tool->onExtract(function (ItemEvent $e) {
            if ($e->getItem() === 'a') {
                $e->getEtl()->skipCurrentItem();
                $e->getEtl()->stopProcess();
            }
        });
        $tool->extractor(fn($input, Etl $etl) => ['a', 'b']);
        $tool->transformer(fn() => yield ['a', 'b']);
        $tool->loader(function ($item, $key, $etl) use (&$target) {
            $target[] = $item;
        });

        $o = $tool->create();
        $o->process('a,b');
        $this->assertCount(0, $target);
    }

    public function testProcessExtractThrowExceptionIgnore(): void
    {
        $target = [];

        $tool = new EtlTool();
        $tool->onExtractException(function (ItemExceptionEvent $e) {
            $e->ignoreException();
        });
        $tool->extractor(function ($input, Etl $etl) {
            foreach (['a', 'b', 'c', 'd'] as $row) {
                if ($row === 'c') {
                    throw new Error('Error');
                }
                yield $row;
            }
        });
        $tool->transformer(fn() => yield ['a', 'b']);
        $tool->loader(function ($item, $key, $etl) use (&$target) {
            $target[] = $item;
        });

        $o = $tool->create();
        $o->process('a,b');
        $this->assertCount(2, $target);
    }

    public function testProcessExtractThrowException(): void
    {
        $target = [];

        $tool = new EtlTool();
        $tool->extractor(function ($input, Etl $etl) {
            foreach (['a', 'b', 'c', 'd'] as $row) {
                if ($row === 'c') {
                    throw new Error('Error');
                }
                yield $row;
            }
        });
        $tool->transformer(fn() => yield ['a', 'b']);
        $tool->loader(function ($item, $key, $etl) use (&$target) {
            $target[] = $item;
        });

        $this->expectException(Error::class);
        $o = $tool->create();
        $o->process('a,b');
        $this->assertCount(2, $target);
    }

    public function testProcessTransformThrowException(): void
    {
        $target = [];

        $tool = new EtlTool();
        $tool->onTransform(function (ItemEvent $e) {
            if ($e->getItem() === 'b') {
                throw new Exception();
            }
        });
        $tool->extractor(fn($input, Etl $etl) => ['a', 'b']);
        $tool->transformer(function () {
            yield ['a', 'b'];
        });

        $tool->loader(function ($item, $key, $etl) use (&$target) {
            $target[] = $item;
        });

        $this->expectException(Exception::class);
        $o = $tool->create();
        $o->process('a,b');
        $this->assertCount(2, $target);
    }
}
