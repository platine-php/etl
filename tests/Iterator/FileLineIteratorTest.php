<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Iterator;

use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Etl\Iterator\FileLineIterator;
use SplFileObject;

/**
 * FileLineIterator class tests
 *
 * @group etl
 * @group iterator
 */
class FileLineIteratorTest extends PlatineTestCase
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
        $o = new FileLineIterator(new SplFileObject($file->url()));

        $res = $o->getIterator();

        $this->assertEquals('foo', $res->current());
    }

    public function testIterateFromFilename(): void
    {
        $file = $this->createVfsFile('data.csv', $this->vfsPath, 'foo');
        $o = FileLineIterator::createFromFilename($file->url());

        $res = $o->getIterator();

        $this->assertEquals('foo', $res->current());
    }
}
