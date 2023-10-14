<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Loader;

use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Etl\Etl;
use Platine\Etl\Iterator\TextLineIterator;
use Platine\Etl\Loader\FileLoader;
use SplFileObject;

/**
 * FileLoader class tests
 *
 * @group etl
 * @group loader
 */
class FileLoaderTest extends PlatineTestCase
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

    public function testLoadDefault(): void
    {
        $generator = new TextLineIterator("foo\n\rbar", true);
        $etl = $this->getMockInstance(Etl::class);

        $file = $this->createVfsFile('data.csv', $this->vfsPath);
        $o = new FileLoader(new SplFileObject($file->url(), 'w'), '');

        $o->load($generator->getIterator(), 'a', $etl);

        $this->assertEquals('foobar', $file->getContent());
    }



    public function testEmptyMethod(): void
    {
        $file = $this->createVfsFile('data.csv', $this->vfsPath, 'foo');
        $o = new FileLoader($file->url());
        $o->init();
        $o->commit(true);
        $o->rollback();
        $this->assertTrue(true);
    }
}
