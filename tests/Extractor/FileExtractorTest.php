<?php

declare(strict_types=1);

namespace Platine\Etl\Test\Loader;

use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Etl\Etl;
use Platine\Etl\Extractor\ExtractorInterface;
use Platine\Etl\Extractor\FileExtractor;

/**
 * FileExtractor class tests
 *
 * @group etl
 * @group extractor
 */
class FileExtractorTest extends PlatineTestCase
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

    public function testExtract(): void
    {
        $file = $this->createVfsFile('data.csv', $this->vfsPath);

        $etl = $this->getMockInstance(Etl::class);
        $contentExtractor = $this->getMockBuilder(ExtractorInterface::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $contentExtractor->expects($this->any())
                ->method('extract')
                ->will($this->returnValue(['foo']));

        $o = new FileExtractor($contentExtractor);

        $this->assertEquals(['foo'], $o->extract($file->url(), $etl));
    }
}
