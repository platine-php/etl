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

use Exception;
use InvalidArgumentException;
use Platine\Etl\Etl;
use Platine\Stdlib\Helper\Json;
use RuntimeException;
use SplFileObject;

/**
 * @class JsonExtractor
 * @package Platine\Etl\Extractor
 */
class JsonExtractor implements ExtractorInterface
{
    public const EXTRACT_AUTO = 0;
    public const EXTRACT_FROM_STRING = 1;
    public const EXTRACT_FROM_FILE = 2;
    public const EXTRACT_FROM_ARRAY = 3;

    /**
     * The extract source type
     * @var int
     */
    protected int $type;

    /**
     * Create new instance
     * @param int $type
     */
    public function __construct(int $type = self::EXTRACT_AUTO)
    {
        $this->type = $type;
    }


    /**
     * {@inheritodc}
     */
    public function extract(mixed $input, Etl $etl, array $options = []): iterable
    {
        $this->setOptions($options);

        switch ($this->type) {
            case self::EXTRACT_FROM_ARRAY:
                return $this->extractFromArray($input);
            case self::EXTRACT_FROM_FILE:
                return $this->extractFromFile($input);
            case self::EXTRACT_FROM_STRING:
                return $this->extractFromString($input);
            case self::EXTRACT_AUTO:
                return $this->extractAuto($input);
        }

        throw new InvalidArgumentException(sprintf(
            'Invalid extract source data type provided [%d], must be one of [%s]',
            $this->type,
            implode(',', [
                self::EXTRACT_AUTO,
                self::EXTRACT_FROM_STRING,
                self::EXTRACT_FROM_FILE,
                self::EXTRACT_FROM_ARRAY
            ])
        ));
    }

    /**
     * Extract source data from array
     * @param array<mixed> $data
     * @return iterable<int|string, mixed>
     */
    protected function extractFromArray(array $data): iterable
    {
        return $data;
    }

    /**
     * Extract source data from string
     * @param string $data
     * @return iterable<int|string, mixed>
     */
    protected function extractFromString(string $data): iterable
    {
        return Json::decode($data, true);
    }

    /**
     * Extract source data from file
     * @param SplFileObject|string $file
     * @return iterable<int|string, mixed>
     */
    protected function extractFromFile(SplFileObject|string $file): iterable
    {
        if ($file instanceof SplFileObject) {
            $file = $file->getPathname();
        }

        if (is_readable($file) === false) {
            throw new RuntimeException(sprintf(
                'File %s is not readable or does not exist',
                $file
            ));
        }

        return Json::decode((string) file_get_contents($file), true);
    }

    /**
     * Extract source data by detect the type
     * @param array<mixed>|string $data
     * @return iterable<int|string, mixed>
     */
    protected function extractAuto(array|string $data): iterable
    {
        if (is_array($data)) {
            return $this->extractFromArray($data);
        }

        try {
            $json = Json::decode($data, true);

            return $this->extractFromArray($json);
        } catch (Exception $e) {
            if (strlen($data) < 3000 && file_exists($data)) {
                return $this->extractFromFile($data);
            }

            throw $e;
        }
    }

    /**
     * Set the options
     * @param array<string, mixed> $options
     * @return $this
     */
    protected function setOptions(array $options): self
    {
        if (isset($options['type']) && is_int($options['type'])) {
            $this->type = $options['type'];
        }

        return $this;
    }
}
