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

use Generator;
use IteratorAggregate;
use Traversable;

/**
 * @class TextLineIterator
 * @package Platine\Etl\Iterator
 * @implements IteratorAggregate<int|string, mixed>
 */
class TextLineIterator implements StringIteratorInterface, IteratorAggregate
{
    /**
     * The content
     * @var string
     */
    protected string $content;

    /**
     * Whether to skip empty line
     * @var bool
     */
    protected bool $skipEmptyLine = true;

    /**
     * Create new instance
     * @param string $content
     * @param bool $skipEmptyLine
     */
    public function __construct(string $content, bool $skipEmptyLine = true)
    {
        $this->content = $content;
        $this->skipEmptyLine = $skipEmptyLine;
    }


    /**
     * {@inheritodc}
     */
    public function getIterator(): Traversable
    {
        if ($this->skipEmptyLine) {
            return $this->strtokIterator();
        }

        return $this->pregSplitIterator();
    }

    /**
     * Uses a regex to split lines.
     * @return Generator|string[]
     */
    protected function pregSplitIterator()
    {
        $lines = (array) preg_split("/((\r?\n)|(\r\n?))/", $this->content);
        foreach ($lines as $line) {
            yield $line;
        }
    }

    /**
     * Uses "strtok" to split lines. Provides better performance, but skips empty lines.
     * @return Generator|string[]
     */
    protected function strtokIterator()
    {
        $tok = strtok($this->content, "\r\n");
        while ($tok !== false) {
            $line = $tok;
            $tok = strtok("\n\r");
            yield $line;
        }
    }
}
