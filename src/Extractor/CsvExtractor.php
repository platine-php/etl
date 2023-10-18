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

namespace Platine\Etl\Extractor;

use InvalidArgumentException;
use Platine\Etl\Etl;
use Platine\Etl\Iterator\CsvFileIterator;
use Platine\Etl\Iterator\CsvKeysAwareIterator;
use Platine\Etl\Iterator\CsvStringIterator;
use SplFileObject;

/**
 * @class CsvExtractor
 * @package Platine\Etl\Extractor
 */
class CsvExtractor implements ExtractorInterface
{
    public const EXTRACT_AUTO = 0;
    public const EXTRACT_FROM_STRING = 1;
    public const EXTRACT_FROM_FILE = 2;

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
     * Whether to create keys (fields)
     * @var bool
     */
    protected bool $createKeys = false;

    /**
     * The extract source type
     * @var int
     */
    protected int $type;

    /**
     * Create new instance
     * @param int $type
     * @param bool $createKeys
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escapeString
     */
    public function __construct(
        int $type = self::EXTRACT_AUTO,
        bool $createKeys = false,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escapeString = '\\'
    ) {
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escapeString = $escapeString;
        $this->createKeys = $createKeys;
        $this->type = $type;
    }

    /**
     * {@inheritodc}
     */
    public function extract($input, Etl $etl, array $options = []): iterable
    {
        $this->setOptions($options);

        switch ($this->type) {
            case self::EXTRACT_FROM_FILE:
                $iterator = $this->extractFromFile($input);
                break;
            case self::EXTRACT_FROM_STRING:
                 $iterator = $this->extractFromString($input);
                break;
            case self::EXTRACT_AUTO:
                $iterator = $this->extractAuto($input);
                break;
            default:
                throw new InvalidArgumentException(sprintf(
                    'Invalid extract source data type provided [%d], must be one of [%s]',
                    $this->type,
                    implode(',', [
                        self::EXTRACT_AUTO,
                        self::EXTRACT_FROM_STRING,
                        self::EXTRACT_FROM_FILE,
                    ])
                ));
        }

        return $this->createKeys ? new CsvKeysAwareIterator($iterator) : $iterator;
    }

    /**
     * Extract source data from string
     * @param string $data
     * @return iterable<int|string, mixed>
     */
    protected function extractFromString(string $data): iterable
    {
        return CsvStringIterator::createFromText(
            $data,
            $this->delimiter,
            $this->enclosure,
            $this->escapeString
        );
    }

    /**
     * Extract source data from file
     * @param SplFileObject|string $file
     * @return iterable<int|string, mixed>
     */
    protected function extractFromFile($file): iterable
    {
        if ($file instanceof SplFileObject) {
            return new CsvFileIterator(
                $file,
                $this->delimiter,
                $this->enclosure,
                $this->escapeString
            );
        }

        return CsvFileIterator::createFromFilename(
            $file,
            $this->delimiter,
            $this->enclosure,
            $this->escapeString
        );
    }

    /**
     * Extract source data by detect the type
     * @param string $data
     * @return iterable<int|string, mixed>
     */
    protected function extractAuto(string $data): iterable
    {
        if (strlen($data) < 3000 && file_exists($data)) {
            return $this->extractFromFile($data);
        }

        return $this->extractFromString($data);
    }

    /**
     * Set the options
     * @param array<string, mixed> $options
     * @return $this
     */
    protected function setOptions(array $options)
    {
        if (isset($options['delimiter'])) {
            $this->delimiter = $options['delimiter'];
        }

        if (isset($options['enclosure'])) {
            $this->enclosure = $options['enclosure'];
        }

        if (isset($options['escape_string'])) {
            $this->escapeString = $options['escape_string'];
        }

        if (isset($options['create_keys']) && is_bool($options['create_keys'])) {
            $this->createKeys = $options['create_keys'];
        }

        return $this;
    }
}
