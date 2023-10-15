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

namespace Platine\Etl\Event;

use Platine\Etl\Etl;
use Platine\Event\Event;

/**
 * @class BaseEvent
 * @package Platine\Etl\Event
 */
class BaseEvent extends Event
{
    /**
     * Fired at the very beginning of the process.
     */
    public const START = 'start';

    /**
     * Fired after an item has been extracted.
     */
    public const EXTRACT = 'extract';

    /**
     * Fired when extracting an item resulted in an exception.
     */
    public const EXTRACT_EXCEPTION = 'extract.exception';

    /**
     * Fired after an item has been transformed.
     */
    public const TRANSFORM = 'transform';

    /**
     * Fired when transforming an item resulted in an exception.
     */
    public const TRANSFORM_EXCEPTION = 'transform.exception';

    /**
     * This event is fired when initializing the loader (just before the 1st item gets loaded).
     */
    public const LOADER_INIT = 'loader.init';

    /**
     * Fired after an item has been loaded.
     */
    public const LOAD = 'load';

    /**
     * Fired when loading an item resulted in an exception.
     */
    public const LOAD_EXCEPTION = 'load.exception';

    /**
     * Fired after an item has been skipped.
     */
    public const SKIP = 'skip';

    /**
     * Fired after an item required the ETL to stop.
     */
    public const STOP = 'stop';

    /**
     * Fired after a flush operation has been completed.
     */
    public const FLUSH = 'flush';

    /**
     * Fired after a rollback operation has been completed.
     */
    public const ROLLBACK = 'rollback';

    /**
     * Fired at the end of the ETL process.
     */
    public const END = 'end';

    /**
     * The ETL instance
     * @var Etl
     */
    protected Etl $etl;

    /**
     * Create new instance
     * @param string $name
     * @param Etl $etl
     */
    public function __construct(string $name, Etl $etl)
    {
        parent::__construct($name);
        $this->etl = $etl;
    }

    /**
     * Return ETL instance
     * @return Etl
     */
    public function getEtl(): Etl
    {
        return $this->etl;
    }
}
