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
 * @class FileLoader
 * @package Platine\Etl\Loader
 */
class FileLoader implements LoaderInterface
{
    /**
     * The file to be used
     * @var SplFileObject
     */
    protected SplFileObject $file;

    /**
     * The End Of Line
     * @var string
     */
    protected string $eol = PHP_EOL;

    /**
     * Create new instance
     * @param SplFileObject|string $file
     * @param string $eol
     */
    public function __construct($file, string $eol = PHP_EOL)
    {
        if (is_string($file)) {
            $file = new SplFileObject($file, 'w');
        }
        $this->file = $file;
        $this->eol = $eol;
    }

        /**
     * {@inheritodc}
     */
    public function init(): void
    {
    }


    /**
     * {@inheritodc}
     */
    public function load(Generator $items, $key, Etl $etl): void
    {
        foreach ($items as $value) {
            $this->file->fwrite($value . $this->eol);
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
