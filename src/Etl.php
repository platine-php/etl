<?php

/**
 * Platine ETL
 *
 * Platine ETL is a library to Extract-Transform-Load Data from various sources
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine ETL
 * Copyright (c) 2019 Benoit POLASZEK
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Platine\Etl;

use EmptyIterator;
use Exception;
use Generator;
use Platine\Etl\Event\BaseEvent;
use Platine\Etl\Event\EndEvent;
use Platine\Etl\Event\FlushEvent;
use Platine\Etl\Event\ItemEvent;
use Platine\Etl\Event\ItemExceptionEvent;
use Platine\Etl\Event\RollbackEvent;
use Platine\Etl\Event\StartEvent;
use Platine\Etl\Exception\EtlException;
use Platine\Etl\Loader\NullLoader;
use Platine\Event\Dispatcher;
use Platine\Event\DispatcherInterface;
use Throwable;

/**
 * @class Etl
 * @package Platine\Etl
 */
class Etl
{
    /**
     * @var callable|null
     */
    protected $extract = null;

    /**
     * Used to transform data
     * @var callable|null
     */
    protected $transform = null;

    /**
     * Used to initialize loader
     * @var callable|null
     */
    protected $init = null;

    /**
     * The loader
     * @var callable
     */
    protected $load;

    /**
     * @var callable|null
     */
    protected $flush = null;

    /**
     * @var callable|null
     */
    protected $rollback = null;

    /**
     * Total to flush
     * @var int|null
     */
    protected ?int $flushCount = null;

    /**
     * Whether to flush data
     * @var bool
     */
    protected bool $isFlush = false;

    /**
     * Whether to skip data
     * @var bool
     */
    protected bool $isSkip = false;

    /**
     * Whether to stop processing data
     * @var bool
     */
    protected bool $isStop = false;

    /**
     * Whether to rollback data
     * @var bool
     */
    protected bool $isRollback = false;

    /**
     * The event dispatcher
     * @var DispatcherInterface
     */
    protected DispatcherInterface $dispatcher;

    /**
     * Create new instance
     * @param callable|null $extract
     * @param callable|null $transform
     * @param callable|null $init
     * @param callable|null $load
     * @param callable|null $flush
     * @param callable|null $rollback
     * @param int|null $flushCount
     * @param DispatcherInterface|null $dispatcher
     */
    public function __construct(
        ?callable $extract = null,
        ?callable $transform = null,
        ?callable $init = null,
        ?callable $load = null,
        ?callable $flush = null,
        ?callable $rollback = null,
        ?int $flushCount = null,
        ?DispatcherInterface $dispatcher = null
    ) {
        $this->extract = $extract;
        $this->transform = $transform ?? $this->defaultTransformer();
        $this->init = $init;
        if ($load === null) {
            $nullLoader = new NullLoader();
            $load = [$nullLoader, 'load'];
        }
        $this->load = $load;
        $this->flush = $flush;
        $this->rollback = $rollback;
        $this->flushCount = $flushCount !== null ? max(1, $flushCount) : null;
        $this->dispatcher = $dispatcher ?? new Dispatcher();
    }

    /**
     * Run the ETL on the given input.
     * @param mixed|null $data
     * @return void
     */
    public function process($data = null): void
    {
        $flushCounter = 0;
        $totalCounter = 0;

        $this->start();
        foreach ($this->extract($data) as $key => $item) {
            if ($this->isSkip) {
                $this->skip($item, $key);
                continue;
            }

            if ($this->isStop) {
                break;
            }

            $transformed = $this->transform($item, $key);

            if ($this->isSkip) {
                $this->skip($item, $key);
                continue;
            }

            if ($this->isStop) {
                break;
            }

            $flushCounter++;
            $totalCounter++;

            if ($totalCounter === 1) {
                $this->initLoader($item, $key);
            }

            $needFlush = ($this->flushCount === null ? false : (($totalCounter % $this->flushCount) === 0));
            $this->load($transformed(), $item, $key, $needFlush, $flushCounter, $totalCounter);
        }

        $this->end($flushCounter, $totalCounter);
    }

    /**
     * Ask the ETl to stop.
     * @param bool $isRollback if the loader should rollback instead of flushing.
     * @return void
     */
    public function stopProcess(bool $isRollback = false): void
    {
        $this->isStop = true;
        $this->isRollback = $isRollback;
    }

    /**
     * Mark the current item to be skipped.
     * @return void
     */
    public function skipCurrentItem(): void
    {
        $this->isSkip = true;
    }

    /**
     * Ask the loader to trigger flush ASAP.
     * @return void
     */
    public function triggerFlush(): void
    {
        $this->isFlush = true;
    }

    /**
     * Process item skip.
     * @param mixed $item
     * @param int|string $key
     * @return void
     */
    protected function skip($item, $key): void
    {
        $this->isSkip = false;
        $this->dispatcher->dispatch(new ItemEvent(BaseEvent::SKIP, $item, $key, $this));
    }

    /**
     * @param mixed $item
     * @param int|string $key
     * @return void
     */
    protected function stop($item, $key): void
    {
        $this->dispatcher->dispatch(new ItemEvent(BaseEvent::STOP, $item, $key, $this));
    }

    /**
     * Start processing
     * @return void
     */
    protected function start(): void
    {
        $this->reset();
        $this->dispatcher->dispatch(new StartEvent($this));
    }

    /**
     * reset ETL
     * @return void
     */
    protected function reset(): void
    {
        $this->isFlush = false;
        $this->isSkip = false;
        $this->isRollback = false;
        $this->isStop = false;
    }

    /**
     * Extract data.
     * @param mixed $data
     * @return iterable<int|string, mixed>
     */
    protected function extract($data): iterable
    {
        $items = $this->extract === null ? $data : ($this->extract)($data, $this);
        if ($items === null) {
            $items = new EmptyIterator();
        }

        if (is_iterable($items) === false) {
            throw new EtlException('Could not extract data');
        }

        try {
            foreach ($items as $key => $item) {
                try {
                    $this->isSkip = false;
                    $this->dispatcher->dispatch(new ItemEvent(BaseEvent::EXTRACT, $item, $key, $this));
                    yield $key => $item;
                } catch (Exception $e) {
                    continue;
                }
            }
        } catch (Throwable $e) {
            /** @var ItemExceptionEvent $event */
            $event = null;
            $this->dispatcher->dispatch(
                new ItemExceptionEvent(BaseEvent::EXTRACT_EXCEPTION, $item ?? null, $key ?? null, $this, $e),
                $event
            );

            if ($event instanceof ItemExceptionEvent && $event->shouldThrowException()) {
                throw $e;
            }
        }
    }

    /**
     * Transform data.
     * @param mixed $item
     * @param int|string $key
     * @return callable
     */
    protected function transform($item, $key): callable
    {
        $tranformed = ($this->transform)($item, $key, $this);
        if (!$tranformed instanceof Generator) {
            throw new EtlException('The transformer must return a generator');
        }

        $output = [];
        try {
            foreach ($tranformed as $key => $value) {
                $output[] = [$key, $value];
            }
            $this->dispatcher->dispatch(new ItemEvent(BaseEvent::TRANSFORM, $item, $key, $this));
        } catch (Exception $e) {
           /** @var ItemExceptionEvent $event */
            $event = null;
            $this->dispatcher->dispatch(
                new ItemExceptionEvent(BaseEvent::TRANSFORM_EXCEPTION, $item ?? null, $key ?? null, $this, $e),
                $event
            );

            if ($event instanceof ItemExceptionEvent && $event->shouldThrowException()) {
                throw $e;
            }
        }

        return static function () use ($output) {
            foreach ($output as [$key, $value]) {
                yield $key => $value;
            }
        };
    }

    /**
     * Init the loader on the 1st item.
     * @param mixed $item
     * @param int|string $key
     * @return void
     */
    protected function initLoader($item, $key): void
    {
        $this->dispatcher->dispatch(new ItemEvent(BaseEvent::LOADER_INIT, $item, $key, $this));
        if ($this->init === null) {
            return;
        }
        ($this->init)();
    }

    /**
     * Load data.
     * @param iterable<int|string, mixed> $data
     * @param mixed $item
     * @param int|string $key
     * @param bool $flush
     * @param int $flushCounter
     * @param int $totalCounter
     * @return void
     */
    protected function load(
        iterable $data,
        $item,
        $key,
        bool $flush,
        int &$flushCounter,
        int &$totalCounter
    ): void {
        try {
            ($this->load)($data, $key, $this);
            $this->dispatcher->dispatch(new ItemEvent(BaseEvent::LOAD, $item, $key, $this));
        } catch (Throwable $e) {
            /** @var ItemExceptionEvent $event */
            $event = null;
            $this->dispatcher->dispatch(
                new ItemExceptionEvent(BaseEvent::LOAD_EXCEPTION, $item ?? null, $key, $this, $e),
                $event
            );

            if ($event instanceof ItemExceptionEvent && $event->shouldThrowException()) {
                throw $e;
            }

            $flushCounter--;
            $totalCounter--;
        }

        $needFlush = $this->isFlush || $flush;
        if ($needFlush) {
            $this->flush($flushCounter, true);
        }
    }

    /**
     * Flush element
     * @param int $flushCounter
     * @param bool $partial
     * @return void
     */
    protected function flush(int &$flushCounter, bool $partial): void
    {
        if ($this->flush === null) {
            return;
        }
        ($this->flush)($partial);
        $this->dispatcher->dispatch(new FlushEvent($this, $flushCounter, $partial));
        $flushCounter = 0;
        $this->isFlush = false;
    }

    /**
     * Restore loader's initial state.
     * @param int $flushCounter
     * @return void
     */
    protected function rollback(int &$flushCounter): void
    {
        if ($this->rollback === null) {
            return;
        }
        ($this->rollback)();
        $this->dispatcher->dispatch(new RollbackEvent($this, $flushCounter));
        $flushCounter = 0;
    }

    /**
     * Process the end of the ETL.
     * @param int $flushCounter
     * @param int $totalCounter
     * @return void
     */
    protected function end(int $flushCounter, int $totalCounter): void
    {
        if ($this->isRollback) {
            $this->rollback($flushCounter);
            $totalCounter = max(0, $totalCounter - $flushCounter);
        } else {
            $this->flush($flushCounter, false);
        }
        $this->dispatcher->dispatch(new EndEvent($this, (int) $totalCounter));
        $this->reset();
    }

    /**
     * The default transformer to use if none is set
     * @return callable
     */
    protected function defaultTransformer(): callable
    {
        return function ($item, $key): Generator {
            yield $key => $item;
        };
    }
}
