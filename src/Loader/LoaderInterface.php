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

namespace Platine\Etl\Loader;

use Generator;
use Platine\Etl\Etl;

/**
 * A loader is responsible to load normalized data into the appropriate target.
 *
 * @class LoaderInterface
 * @package Platine\Etl\Loader
 */
interface LoaderInterface
{
    /**
     * Init loader (start a transaction, if supported) and reset loader state.
     * @param array<string, mixed> $options additional options
     * @return void
     */
    public function init(array $options = []): void;


    /**
     * Load elements
     * @param Generator $items
     * @param int|string $key
     * @param Etl $etl
     * @return void
     */
    public function load(Generator $items, $key, Etl $etl): void;

    /**
     * Flush elements (if supported).
     * @param bool $partial whether or not there remains elements to process.
     * @return void
     */
    public function commit(bool $partial): void;

    /**
     * Rollback (if supported).
     * @return void
     */
    public function rollback(): void;
}
