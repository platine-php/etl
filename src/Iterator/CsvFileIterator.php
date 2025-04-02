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

use Countable;
use FilterIterator;
use SplFileObject;

/**
 * @class CsvFileIterator
 * @package Platine\Etl\Iterator
 */
class CsvFileIterator extends FilterIterator implements Countable, CsvIteratorInterface
{
    /**
     * The file to be used
     * @var SplFileObject
     */
    protected SplFileObject $file;

    /**
     * The total record in CSV
     * @var int
     */
    protected int $totalLine = 0;

    /**
     * Create new instance
     * @param SplFileObject $file
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escapeString
     */
    public function __construct(
        SplFileObject $file,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escapeString = '\\'
    ) {
        $this->file = $file;
        $this->file->setCsvControl($delimiter, $enclosure, $escapeString);
        $this->file->setFlags(SplFileObject::READ_CSV);

        parent::__construct($this->file);
    }


    /**
     * Create file object from filename
     * @param string $filename
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escapeString
     * @return self
     */
    public static function createFromFilename(
        string $filename,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escapeString = '\\'
    ): self {
        return new static(
            new SplFileObject($filename, 'r'),
            $delimiter,
            $enclosure,
            $escapeString
        );
    }

    /**
     * {@inheritodc}
     */
    public function accept(): bool
    {
        $current = $this->getInnerIterator()->current();
        if (!is_array($current)) {
            return false;
        }

        return count(
            array_filter(
                $current,
                function ($cell) {
                    return $cell !== null;
                }
            )
        ) > 0;
    }


    /**
     * {@inheritodc}
     */
    public function count(): int
    {
        if ($this->totalLine === 0) {
            $this->rewind();

            $this->totalLine = count(iterator_to_array($this));
        }

        return $this->totalLine;
    }
}
