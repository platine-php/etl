<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Iterator;

use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Etl\Iterator\CsvFileIterator;
use SplFileObject;

/**
 * CsvFileIterator class tests
 *
 * @group etl
 * @group iterator
 */
class CsvFileIteratorTest extends PlatineTestCase
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

    public function testIterateDefault(): void
    {
        $file = $this->createVfsFile('data.csv', $this->vfsPath, 'foo');
        $o = new CsvFileIterator(new SplFileObject($file->url()));

        $this->assertNull($o->current());
        $this->assertEquals(1, $o->count());
        $this->assertFalse($o->accept());
    }

    public function testIterateFromFilename(): void
    {
        $file = $this->createVfsFile('data.csv', $this->vfsPath, 'foo');
        $o = CsvFileIterator::createFromFilename($file->url());

        $this->assertNull($o->current());
        $this->assertEquals(1, $o->count());
        $this->assertFalse($o->accept());
    }
}
