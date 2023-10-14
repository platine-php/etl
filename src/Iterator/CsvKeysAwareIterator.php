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

namespace Platine\Etl\Iterator;

use IteratorAggregate;
use Traversable;

/**
 * @class CsvKeysAwareIterator
 * @package Platine\Etl\Iterator
 * @implements IteratorAggregate<int|string, mixed>
 */
class CsvKeysAwareIterator implements IteratorAggregate, CsvIteratorInterface
{
    /**
     * The CSV iterator instance
     * @var CsvIteratorInterface<int|string, mixed>
     */
    protected CsvIteratorInterface $iterator;

    /**
     * The CSV keys (fields)
     * @var string[]
     */
    protected array $keys = [];

    /**
     * Whether to skip first line
     * @var bool
     */
    protected bool $skipFirstLine = true;

    /**
     * Whether already started
     * @var bool
     */
    protected bool $started = false;

    /**
     * Create new instance
     * @param CsvIteratorInterface $iterator
     * @param string[] $keys
     * @param bool $skipFirstLine
     */
    public function __construct(
        CsvIteratorInterface $iterator,
        array $keys = [],
        bool $skipFirstLine = true
    ) {
        $this->iterator = $iterator;
        $this->keys = $keys;
        $this->skipFirstLine = $skipFirstLine;
    }

    /**
     * {@inheritodc}
     */
    public function getIterator(): Traversable
    {
        foreach ($this->iterator as $value) {
            if ($this->started === false) {
                $this->started = true;
                if (count($this->keys) === 0) {
                    $this->keys = $value;
                }

                if ($this->skipFirstLine === true) {
                    continue;
                }
            }

            yield self::combine($this->keys, $value);
        }
    }

    /**
     * Combine keys and values
     * @param string[] $keys
     * @param array<string, mixed> $values
     * @return array<int|string, mixed>
     */
    protected static function combine(array $keys, array $values): array
    {
        $nbKeys = count($keys);
        $nbValues = count($values);

        if ($nbKeys < $nbValues) {
            return (array) array_combine(
                $keys,
                array_slice(array_values($values), 0, $nbKeys)
            );
        }

        if ($nbKeys > $nbValues) {
            return (array) array_combine(
                $keys,
                array_merge($values, (array) array_fill(0, $nbKeys - $nbValues, null))
            );
        }

        return (array) array_combine($keys, $values);
    }
}
