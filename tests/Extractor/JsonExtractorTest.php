<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Loader;

use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Etl\Etl;
use Platine\Etl\Extractor\JsonExtractor;
use RuntimeException;
use SplFileObject;

/**
 * JsonExtractor class tests
 *
 * @group etl
 * @group extractor
 */
class JsonExtractorTest extends PlatineTestCase
{
    protected $vfsRoot;
    protected $vfsPath;

    protected function setUp(): void
    {
        parent::setUp();
        //need setup for each test
        $this->vfsRoot = vfsStream::setup();
        $this->vfsPath = vfsStream::newDirectory('my_tests')->at($this->vfsRoot);
    }

    public function testExtractFromArray(): void
    {
        $etl = $this->getMockInstance(Etl::class);

        $o = new JsonExtractor(JsonExtractor::EXTRACT_FROM_ARRAY);

        $this->assertEquals(['foo'], $o->extract(['foo'], $etl));
    }

    public function testExtractFromFile(): void
    {
        $file = $this->createVfsFile('data.csv', $this->vfsPath, '{"a":"b"}');

        $etl = $this->getMockInstance(Etl::class);

        $o = new JsonExtractor(JsonExtractor::EXTRACT_FROM_FILE);

        $this->assertEquals(['a' => 'b'], $o->extract($file->url(), $etl));
    }

    public function testExtractFromFileSplFileObject(): void
    {
        $file = $this->createVfsFile('data.csv', $this->vfsPath, '{"a":"b"}');

        $etl = $this->getMockInstance(Etl::class);

        $o = new JsonExtractor(JsonExtractor::EXTRACT_FROM_FILE);

        $this->assertEquals(['a' => 'b'], $o->extract(new SplFileObject($file->url()), $etl));
    }

    public function testExtractFromFileNotReadable(): void
    {
        global $mock_is_readable_to_false;

        $mock_is_readable_to_false = true;
        $file = $this->createVfsFile('data.csv', $this->vfsPath, '{"a":"b"}');

        $etl = $this->getMockInstance(Etl::class);

        $o = new JsonExtractor(JsonExtractor::EXTRACT_FROM_FILE);

        $this->expectException(RuntimeException::class);
        $o->extract($file->url(), $etl);
    }

    public function testExtractFromString(): void
    {
        $etl = $this->getMockInstance(Etl::class);

        $o = new JsonExtractor(JsonExtractor::EXTRACT_FROM_STRING);

        $this->assertEquals(['a' => 'b'], $o->extract('{"a":"b"}', $etl));
    }

    public function testExtractAutoString(): void
    {
        $etl = $this->getMockInstance(Etl::class);

        $o = new JsonExtractor(JsonExtractor::EXTRACT_AUTO);

        $this->assertEquals(['a' => 'b'], $o->extract('{"a":"b"}', $etl));
    }

    public function testExtractAutoArray(): void
    {
        $etl = $this->getMockInstance(Etl::class);

        $o = new JsonExtractor(JsonExtractor::EXTRACT_AUTO);

        $this->assertEquals(['foo'], $o->extract(['foo'], $etl));
    }

    public function testExtractAutoFile(): void
    {
        $file = $this->createVfsFile('data.csv', $this->vfsPath, '{"a":"b"}');

        $etl = $this->getMockInstance(Etl::class);

        $o = new JsonExtractor(JsonExtractor::EXTRACT_AUTO);

        $this->assertEquals(['a' => 'b'], $o->extract($file->url(), $etl));
    }

    public function testExtractAutoInvalidType(): void
    {
        $etl = $this->getMockInstance(Etl::class);

        $o = new JsonExtractor(JsonExtractor::EXTRACT_AUTO);

        $this->expectException(InvalidArgumentException::class);
        $o->extract('a', $etl);
    }

    public function testExtractInvalidExtractType(): void
    {
        $etl = $this->getMockInstance(Etl::class);

        $o = new JsonExtractor(55);

        $this->expectException(InvalidArgumentException::class);
        $o->extract('a', $etl);
    }
}
