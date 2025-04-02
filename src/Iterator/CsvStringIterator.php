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
 * @class CsvStringIterator
 * @package Platine\Etl\Iterator
 * @implements IteratorAggregate<int|string, mixed>
 */
class CsvStringIterator implements IteratorAggregate, CsvIteratorInterface
{
    /**
     * The String iterator instance
     * @var StringIteratorInterface<int|string, mixed>
     */
    protected StringIteratorInterface $iterator;

    /**
     * The CSV delimiter
     * @var string
     */
    protected string $delimiter = ',';

    /**
     * The CSV enclosure
     * @var string
     */
    protected string $enclosure = '"';

    /**
     * The CSV escape string
     * @var string
     */
    protected string $escapeString = '\\';

    /**
     * Create new instance
     * @param StringIteratorInterface<int|string, mixed> $iterator
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escapeString
     */
    public function __construct(
        StringIteratorInterface $iterator,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escapeString = '\\'
    ) {
        $this->iterator = $iterator;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escapeString = $escapeString;
    }

    /**
     * {@inheritodc}
     */
    public function getIterator(): Traversable
    {
        foreach ($this->iterator as $line) {
            yield str_getcsv(
                $line,
                $this->delimiter,
                $this->enclosure,
                $this->escapeString
            );
        }
    }

    /**
     * Create from text
     * @param string $text
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escapeString
     * @return self
     */
    public static function createFromText(
        string $text,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escapeString = '\\'
    ): self {
        return new self(
            new TextLineIterator($text, true),
            $delimiter,
            $enclosure,
            $escapeString
        );
    }
}
