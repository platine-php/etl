<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Loader;

use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Etl\Etl;
use Platine\Etl\Extractor\CsvExtractor;
use Platine\Etl\Iterator\CsvFileIterator;
use Platine\Etl\Iterator\CsvStringIterator;
use SplFileObject;

/**
 * CsvExtractor class tests
 *
 * @group etl
 * @group extractor
 */
class CsvExtractorTest extends PlatineTestCase
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

    public function testExtractFromFile(): void
    {
        $file = $this->createVfsFile('data.csv', $this->vfsPath, "a,b\n1,2");

        $etl = $this->getMockInstance(Etl::class);

        $o = new CsvExtractor(CsvExtractor::EXTRACT_FROM_FILE);
        /** @var CsvFileIterator $res */
        $res = $o->extract($file->url(), $etl);

        $this->assertInstanceOf(CsvFileIterator::class, $res);
        $this->assertEquals(2, $res->count());
        $res->rewind();
        $this->assertTrue($res->valid());
        $this->assertEquals(['a', 'b'], $res->current());
        $res->next();
        $this->assertEquals(['1', '2'], $res->current());
    }

    public function testExtractFromFileSplFileObject(): void
    {
        $file = $this->createVfsFile('data.csv', $this->vfsPath, '1,2');

        $etl = $this->getMockInstance(Etl::class);

        $o = new CsvExtractor(CsvExtractor::EXTRACT_FROM_FILE);

        /** @var CsvFileIterator $res */
        $res = $o->extract(new SplFileObject($file->url()), $etl);

        $this->assertInstanceOf(CsvFileIterator::class, $res);
        $this->assertEquals(1, $res->count());
        $res->rewind();
        $this->assertTrue($res->valid());
        $this->assertEquals(['1', '2'], $res->current());
    }

    public function testExtractFromString(): void
    {
        $etl = $this->getMockInstance(Etl::class);

        $o = new CsvExtractor(CsvExtractor::EXTRACT_FROM_STRING);

        /** @var CsvStringIterator $res */
        $res = $o->extract("a,b\n1,2", $etl);

        $this->assertInstanceOf(CsvStringIterator::class, $res);
        $res = $res->getIterator();
        $this->assertTrue($res->valid());
        $this->assertEquals(['a', 'b'], $res->current());
    }

    public function testExtractAutoString(): void
    {
        $etl = $this->getMockInstance(Etl::class);

        $o = new CsvExtractor(CsvExtractor::EXTRACT_AUTO);

        /** @var CsvStringIterator $res */
        $res = $o->extract("a,b\n1,2", $etl);

        $this->assertInstanceOf(CsvStringIterator::class, $res);
        $res = $res->getIterator();
        $this->assertTrue($res->valid());
        $this->assertEquals(['a', 'b'], $res->current());
    }

    public function testExtractAutoFile(): void
    {
        $file = $this->createVfsFile('data.csv', $this->vfsPath, "a,b\n1,2");

        $etl = $this->getMockInstance(Etl::class);

        $o = new CsvExtractor(CsvExtractor::EXTRACT_AUTO);

        /** @var CsvFileIterator $res */
        $res = $o->extract($file->url(), $etl);

        $this->assertInstanceOf(CsvFileIterator::class, $res);
        $this->assertEquals(2, $res->count());
        $res->rewind();
        $this->assertTrue($res->valid());
        $this->assertEquals(['a', 'b'], $res->current());
    }

    public function testExtractInvalidExtractType(): void
    {
        $etl = $this->getMockInstance(Etl::class);

        $o = new CsvExtractor(55);

        $this->expectException(InvalidArgumentException::class);
        $o->extract('a', $etl);
    }
}
