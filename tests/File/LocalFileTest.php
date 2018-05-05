<?php

namespace ElevenLabs\DockerHostManager\File;

use ElevenLabs\DockerHostManager\File\Exception\FileDoesNotExist;
use ElevenLabs\DockerHostManager\File\Exception\FileNotWritable;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;

class LocalFileTest extends TestCase
{
    private $rootDirectory;

    public function setUp()
    {
        $this->rootDirectory = vfsStream::setup('etc', 444);
    }

    private function addFile($name, $content = '', $permission = 0644): vfsStreamFile
    {
        $file = vfsStream::newFile($name, $permission)->withContent($content);
        $this->rootDirectory->addChild($file);

        return $file;
    }

    /** @test */
    public function it provide the content of a file()
    {
        $filename  = $this->addFile('hosts', 'some content')->url();
        $localFile = new LocalFile($filename);

        assertThat($localFile->getContents(), equalTo('some content'));
    }

    /** @test */
    public function it can update the content of a file()
    {
        $filename  = $this->addFile('hosts')->url();
        $localFile = new LocalFile($filename);
        $localFile->putContents('new content');

        assertThat($localFile->getContents(), equalTo('new content'));
    }

    /** @test */
    public function it return true if a file exists()
    {
        $filename  = $this->addFile('hosts')->url();
        $localFile = new LocalFile($filename);

        assertTrue($localFile->exists());
    }

    /** @test */
    public function it return false when a file does not exist()
    {
        $localFile = new LocalFile('/nowhere');

        assertFalse($localFile->exists());
    }

    /** @test */
    public function it throw an exception when trying to get the content of the file which do not exist()
    {
        $this->expectException(FileDoesNotExist::class);

        $localFile = new LocalFile('/nowhere');
        $localFile->getContents();
    }

    /** @test */
    public function it throw an exception when trying to update the content of the file which do not exist()
    {
        $this->expectException(FileDoesNotExist::class);

        $localFile = new LocalFile('/nowhere');
        $localFile->putContents('new content');
    }

    /** @test */
    public function it throw an exception when trying to update the content of a file which is not writable()
    {
        $this->expectException(FileNotWritable::class);

        $filename = $this->addFile('hosts', '', 0444)->url();

        $localFile = new LocalFile($filename);
        $localFile->putContents('new content');
    }
}