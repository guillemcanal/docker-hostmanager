<?php

namespace ElevenLabs\DockerHostManager\File;

use ElevenLabs\DockerHostManager\File\Exception\FileDoesNotExist;
use ElevenLabs\DockerHostManager\File\Exception\CouldNotWriteFile;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;

class LocalFileTest extends TestCase
{
    private $rootDirectory;

    public function setUp()
    {
        $this->rootDirectory = vfsStream::setup('etc', 0444);
    }

    private function addFile($name, $content = '', $permission = 0644): vfsStreamFile
    {
        $file = vfsStream::newFile($name, $permission)->withContent($content);
        $this->rootDirectory->addChild($file);

        return $file;
    }

    /** @test */
    public function it read the content of a file()
    {
        $filename  = $this->addFile('hosts', 'some content')->url();
        $localFile = new LocalFile($filename);

        assertThat($localFile->read(), equalTo('some content'));
    }

    /** @test */
    public function it can update the content of a file()
    {
        $filename  = $this->addFile('hosts')->url();
        $localFile = new LocalFile($filename);
        $localFile->put('new content');

        assertThat($localFile->read(), equalTo('new content'));
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
        $localFile = new LocalFile('vfs://etc/i-do-not-exist');

        assertFalse($localFile->exists());
    }

    /** @test */
    public function it throw an exception when trying to get the content of the file which do not exist()
    {
        $this->expectException(FileDoesNotExist::class);

        $localFile = new LocalFile('vfs://etc/i-do-not-exist');
        $localFile->read();
    }

    /** @test */
    public function it throw an exception when trying to update the content of the file which do not exist()
    {
        $this->expectException(CouldNotWriteFile::class);

        $localFile = new LocalFile('vfs://etc/i-do-not-exist');
        $localFile->put('new content');
    }

    /** @test */
    public function it throw an exception when trying to update the content of a file which is not writable()
    {
        $this->expectException(CouldNotWriteFile::class);

        $filename = $this->addFile('hosts', '', 0444)->url();

        $localFile = new LocalFile($filename);
        $localFile->put('new content');
    }

    /** @test */
    public function it can be constructed with a file path()
    {
        $localFile = LocalFile::getFile('hello.txt');

        assertThat($localFile, isInstanceOf(LocalFile::class));
    }

    /** @test */
    public function it throw an exception when to root directory is ot writable()
    {
        $this->expectException(CouldNotWriteFile::class);
        $this->expectExceptionMessage('Unable to create file in vfs://etc/foo');

        $localFile = LocalFile::getFile('vfs://etc/foo/bar.txt');
        $localFile->put('');
    }
}