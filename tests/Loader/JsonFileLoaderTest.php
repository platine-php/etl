<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Loader;

use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Etl\Etl;
use Platine\Etl\Iterator\CsvKeysAwareIterator;
use Platine\Etl\Iterator\CsvStringIterator;
use Platine\Etl\Iterator\TextLineIterator;
use Platine\Etl\Loader\JsonFileLoader;
use RuntimeException;
use SplFileObject;

/**
 * JsonFileLoader class tests
 *
 * @group etl
 * @group loader
 */
class JsonFileLoaderTest extends PlatineTestCase
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

        $generator = new CsvKeysAwareIterator($iterator, ['a', 'b'], true);

        $etl = $this->getMockInstance(Etl::class);

        $file = $this->createVfsFile('data.csv', $this->vfsPath);
        $o = new JsonFileLoader(new SplFileObject($file->url(), 'w'));

        $o->load($generator->getIterator(), 'a', $etl);
        $o->commit(false);

        $this->assertEquals('[{"a":"1","b":"2"}]', $file->getContent());
    }

    public function testLoadException(): void
    {
        global $mock_json_encode;

        $mock_json_encode = true;
        $textIterator = new TextLineIterator("a,b\n\r1,2");
        $iterator = new CsvStringIterator($textIterator);

        $generator = new CsvKeysAwareIterator($iterator, ['a', 'b'], true);

        $etl = $this->getMockInstance(Etl::class);
        $file = $this->createVfsFile('data.csv', $this->vfsPath);
        $o = new JsonFileLoader(new SplFileObject($file->url(), 'w'));

        $this->expectException(RuntimeException::class);
        $o->load($generator->getIterator(), 'a', $etl);
        $o->commit(false);

        $this->assertEquals('[{"a":"1","b":"2"}]', $file->getContent());
    }

    public function testEmptyMethod(): void
    {
        $file = $this->createVfsFile('data.csv', $this->vfsPath, 'foo');
        $o = new JsonFileLoader($file->url());
        $o->init();
        $o->commit(true);
        $o->rollback();
        $this->assertTrue(true);
    }
}
