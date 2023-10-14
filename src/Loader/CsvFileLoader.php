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
use SplFileObject;

/**
 * @class CsvFileLoader
 * @package Platine\Etl\Loader
 */
class CsvFileLoader implements LoaderInterface
{
    /**
     * The file to be used
     * @var SplFileObject
     */
    protected SplFileObject $file;

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
     * The CSV keys (fields)
     * @var string[]
     */
    protected array $keys = [];


    /**
     * Create new instance
     * @param SplFileObject|string $file
     * @param array $keys
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escapeString
     */
    public function __construct(
        $file,
        array $keys = [],
        string $delimiter = ',',
        string $enclosure = '"',
        string $escapeString = '\\'
    ) {
        if (is_string($file)) {
            $file = new SplFileObject($file, 'w');
        }
        $this->file = $file;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escapeString = $escapeString;
        $this->keys = $keys;
    }



    /**
     * {@inheritodc}
     */
    public function init(): void
    {
        if (count($this->keys) > 0) {
            $this->file->fputcsv(
                $this->keys,
                $this->delimiter,
                $this->enclosure,
                $this->escapeString
            );
        }
    }


    /**
     * {@inheritodc}
     */
    public function load(Generator $items, $key, Etl $etl): void
    {
        foreach ($items as $value) {
            $this->file->fputcsv(
                $value,
                $this->delimiter,
                $this->enclosure,
                $this->escapeString
            );
        }
    }

    /**
     * {@inheritodc}
     */
    public function commit(bool $partial): void
    {
    }

    /**
     * {@inheritodc}
     */
    public function rollback(): void
    {
    }
}
