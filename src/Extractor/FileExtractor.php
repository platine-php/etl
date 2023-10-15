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

use Platine\Etl\Etl;

/**
 * @class FileExtractor
 * @package Platine\Etl\Extractor
 */
class FileExtractor implements ExtractorInterface
{
    /**
     * The extractor used to extract the content
     * @var ExtractorInterface
     */
    protected ExtractorInterface $contentExtractor;

    /**
     * Create new instance
     * @param ExtractorInterface $contentExtractor
     */
    public function __construct(ExtractorInterface $contentExtractor)
    {
        $this->contentExtractor = $contentExtractor;
    }


    /**
     * {@inheritodc}
     * @param string $input
     */
    public function extract($input, Etl $etl): iterable
    {
        return $this->contentExtractor->extract((string) file_get_contents($input), $etl);
    }
}
