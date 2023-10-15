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

use InvalidArgumentException;
use Platine\Etl\Event\BaseEvent;
use Platine\Etl\Extractor\ExtractorInterface;
use Platine\Etl\Loader\LoaderInterface;
use Platine\Etl\Transformer\TransformerInterface;
use Platine\Event\Dispatcher;
use Platine\Event\DispatcherInterface;
use Platine\Event\ListenerInterface;
use RuntimeException;

/**
 * @class EtlTool
 * @package Platine\Etl
 */
class EtlTool
{
    /**
     * @var callable|null
     */
    protected $extractor = null;

    /**
     * Used to transform data
     * @var callable|null
     */
    protected $transformer = null;

    /**
     * Used to initialize loader
     * @var callable|null
     */
    protected $initLoader = null;

    /**
     * The loader
     * @var callable
     */
    protected $loader;

    /**
     * @var callable|null
     */
    protected $committer = null;

    /**
     * @var callable|null
     */
    protected $restorer = null;

    /**
     * Total to flush
     * @var int|null
     */
    protected ?int $flushCount = null;

    /**
     * The event dispatcher
     * @var DispatcherInterface
     */
    protected DispatcherInterface $dispatcher;

    /**
     * Create new instance
     * @param ExtractorInterface|callable|null $extractor
     * @param TransformerInterface|callable|null $transformer
     * @param LoaderInterface|callable|null $loader
     * @param DispatcherInterface|null $dispatcher
     */
    public function __construct(
        $extractor = null,
        $transformer = null,
        $loader = null,
        ?DispatcherInterface $dispatcher = null
    ) {
        if ($extractor !== null) {
            $this->extractor($extractor);
        }
        if ($transformer !== null) {
            $this->transformer($transformer);
        }

        if ($loader !== null) {
            $this->loader($loader);
        }

        $this->dispatcher = $dispatcher ?? new Dispatcher();
    }

    /**
     * The extractor to be used
     * @param ExtractorInterface|callable|null $extractor
     * @return $this
     */
    public function extractor($extractor): self
    {
        if ($extractor instanceof ExtractorInterface) {
            $this->extractor = [$extractor, 'extract'];

            return $this;
        }

        if (is_callable($extractor) || $extractor === null) {
            $this->extractor = $extractor;

            return $this;
        }

        throw new InvalidArgumentException(sprintf(
            'The extractor could be callable, null or instance of %s, but got %s',
            ExtractorInterface::class,
            is_object($extractor) ? get_class($extractor) : gettype($extractor)
        ));
    }

    /**
     * The transformer to be used
     * @param TransformerInterface|callable|null $transformer
     * @return $this
     */
    public function transformer($transformer): self
    {
        if ($transformer instanceof TransformerInterface) {
            $this->transformer = [$transformer, 'transform'];

            return $this;
        }

        if (is_callable($transformer) || $transformer === null) {
            $this->transformer = $transformer;

            return $this;
        }

        throw new InvalidArgumentException(sprintf(
            'The transformer could be  callable, null or instance of %s, but got %s',
            TransformerInterface::class,
            is_object($transformer) ? get_class($transformer) : gettype($transformer)
        ));
    }

    /**
     * The loader to be used
     * @param LoaderInterface|callable $loader
     * @return $this
     */
    public function loader($loader): self
    {
        if ($loader instanceof LoaderInterface) {
            $this->loader = [$loader, 'load'];
            $this->initLoader = [$loader, 'init'];
            $this->committer = [$loader, 'commit'];
            $this->restorer = [$loader, 'rollback'];

            return $this;
        }

        if (is_callable($loader)) {
            $this->loader = $loader;

            return $this;
        }

        throw new InvalidArgumentException(sprintf(
            'The loader could be  callable or instance of %s, but got %s',
            LoaderInterface::class,
            is_object($loader) ? get_class($loader) : gettype($loader)
        ));
    }

    /**
     *
     * @param int|null $flushCount
     * @return $this
     */
    public function setFlushCount(?int $flushCount): self
    {
        $this->flushCount = $flushCount;
        return $this;
    }

    /**
     * Register a listener for start event
     *
     * @param ListenerInterface|callable $listener the Listener interface or any callable
     * @param int $priority the listener execution priority
     * @return $this
     */
    public function onStart($listener, int $priority = DispatcherInterface::PRIORITY_DEFAULT): self
    {
        $this->dispatcher->addListener(BaseEvent::START, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for extract event
     *
     * @param ListenerInterface|callable $listener the Listener interface or any callable
     * @param int $priority the listener execution priority
     * @return $this
     */
    public function onExtract($listener, int $priority = DispatcherInterface::PRIORITY_DEFAULT): self
    {
        $this->dispatcher->addListener(BaseEvent::EXTRACT, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for extract exception event
     *
     * @param ListenerInterface|callable $listener the Listener interface or any callable
     * @param int $priority the listener execution priority
     * @return $this
     */
    public function onExtractException($listener, int $priority = DispatcherInterface::PRIORITY_DEFAULT): self
    {
        $this->dispatcher->addListener(BaseEvent::EXTRACT_EXCEPTION, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for transform event
     *
     * @param ListenerInterface|callable $listener the Listener interface or any callable
     * @param int $priority the listener execution priority
     * @return $this
     */
    public function onTransform($listener, int $priority = DispatcherInterface::PRIORITY_DEFAULT): self
    {
        $this->dispatcher->addListener(BaseEvent::TRANSFORM, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for transform exception event
     *
     * @param ListenerInterface|callable $listener the Listener interface or any callable
     * @param int $priority the listener execution priority
     * @return $this
     */
    public function onTransformException($listener, int $priority = DispatcherInterface::PRIORITY_DEFAULT): self
    {
        $this->dispatcher->addListener(BaseEvent::TRANSFORM_EXCEPTION, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for loader init event
     *
     * @param ListenerInterface|callable $listener the Listener interface or any callable
     * @param int $priority the listener execution priority
     * @return $this
     */
    public function onLoaderInit($listener, int $priority = DispatcherInterface::PRIORITY_DEFAULT): self
    {
        $this->dispatcher->addListener(BaseEvent::LOADER_INIT, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for loader load event
     *
     * @param ListenerInterface|callable $listener the Listener interface or any callable
     * @param int $priority the listener execution priority
     * @return $this
     */
    public function onLoad($listener, int $priority = DispatcherInterface::PRIORITY_DEFAULT): self
    {
        $this->dispatcher->addListener(BaseEvent::LOAD, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for loader load exception event
     *
     * @param ListenerInterface|callable $listener the Listener interface or any callable
     * @param int $priority the listener execution priority
     * @return $this
     */
    public function onLoadException($listener, int $priority = DispatcherInterface::PRIORITY_DEFAULT): self
    {
        $this->dispatcher->addListener(BaseEvent::LOAD_EXCEPTION, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for flush event
     *
     * @param ListenerInterface|callable $listener the Listener interface or any callable
     * @param int $priority the listener execution priority
     * @return $this
     */
    public function onFlush($listener, int $priority = DispatcherInterface::PRIORITY_DEFAULT): self
    {
        $this->dispatcher->addListener(BaseEvent::FLUSH, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for skip event
     *
     * @param ListenerInterface|callable $listener the Listener interface or any callable
     * @param int $priority the listener execution priority
     * @return $this
     */
    public function onSkip($listener, int $priority = DispatcherInterface::PRIORITY_DEFAULT): self
    {
        $this->dispatcher->addListener(BaseEvent::SKIP, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for stop event
     *
     * @param ListenerInterface|callable $listener the Listener interface or any callable
     * @param int $priority the listener execution priority
     * @return $this
     */
    public function onStop($listener, int $priority = DispatcherInterface::PRIORITY_DEFAULT): self
    {
        $this->dispatcher->addListener(BaseEvent::STOP, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for rollback event
     *
     * @param ListenerInterface|callable $listener the Listener interface or any callable
     * @param int $priority the listener execution priority
     * @return $this
     */
    public function onRollback($listener, int $priority = DispatcherInterface::PRIORITY_DEFAULT): self
    {
        $this->dispatcher->addListener(BaseEvent::ROLLBACK, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for end event
     *
     * @param ListenerInterface|callable $listener the Listener interface or any callable
     * @param int $priority the listener execution priority
     * @return $this
     */
    public function onEnd($listener, int $priority = DispatcherInterface::PRIORITY_DEFAULT): self
    {
        $this->dispatcher->addListener(BaseEvent::END, $listener, $priority);

        return $this;
    }

    /**
     * Create ETL object
     * @return Etl
     */
    public function create(): Etl
    {
        if ($this->loader === null) {
            throw new RuntimeException('The loader not defined');
        }

        if ($this->flushCount !== null && $this->flushCount <= 0) {
            throw new RuntimeException('The flush count must be null or greather than zero (0)');
        }

        return new Etl(
            $this->extractor,
            $this->transformer,
            $this->initLoader,
            $this->loader,
            $this->committer,
            $this->restorer,
            $this->flushCount,
            $this->dispatcher
        );
    }
}
