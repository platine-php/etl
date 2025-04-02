<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Loader;

use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Etl\Etl;
use Platine\Etl\Iterator\CsvKeysAwareIterator;
use Platine\Etl\Iterator\CsvStringIterator;
use Platine\Etl\Iterator\TextLineIterator;
use Platine\Etl\Loader\CsvFileLoader;
use SplFileObject;

/**
 * CsvFileLoader class tests
 *
 * @group etl
 * @group loader
 */
class CsvFileLoaderTest extends PlatineTestCase
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
        $textIterator = new TextLineIterator("a,b\n\r1,2");
        $iterator = new CsvStringIterator($textIterator);

        $generator = new CsvKeysAwareIterator($iterator, ['a', 'b'], false);

        $etl = $this->getMockInstance(Etl::class);

        $file = $this->createVfsFile('data.csv', $this->vfsPath);
        $o = new CsvFileLoader(new SplFileObject($file->url(), 'w'), ['a', 'b']);

        $o->load($generator->getIterator(), 'a', $etl);

        $this->assertEquals("a,b\n1,2\n", $file->getContent());
    }

    public function testLoadUsingOptions(): void
    {
        $textIterator = new TextLineIterator("a,b\n\r1,2");
        $iterator = new CsvStringIterator($textIterator);

        $generator = new CsvKeysAwareIterator($iterator, ['a', 'b'], false);

        $etl = $this->getMockInstance(Etl::class);

        $file = $this->createVfsFile('data.csv', $this->vfsPath);
        $o = new CsvFileLoader(new SplFileObject($file->url(), 'w'), ['a', 'b']);
        $o->init([
            'delimiter' => ';',
            'enclosure' => '"',
            'escape_string' => '\\',
            'keys' => [],
        ]);
        $o->load($generator->getIterator(), 'a', $etl);

        $utf8Bom = "\xEF\xBB\xBF";
        $this->assertEquals("${utf8Bom}a;b\n1;2\n", $file->getContent());
    }



    public function testEmptyMethod(): void
    {
        $file = $this->createVfsFile('data.csv', $this->vfsPath, 'foo');
        $o = new CsvFileLoader($file->url(), ['a', 'b']);
        $o->init();
        $o->commit(true);
        $o->rollback();
        $this->assertTrue(true);
    }
}
