<?php

namespace ElevenLabs\DockerHostManager\File;

use ElevenLabs\DockerHostManager\File\Exception\UnableToCreateDirectory;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class LocalDirectoryTest extends TestCase
{
    /** @test */
    public function it implements the directory interface()
    {
        assertThat(new LocalDirectory('foo'), isInstanceOf(Directory::class));
    }

    /** @test */
    public function it can be constructed statically()
    {
        assertThat(LocalDirectory::get('foo'), isInstanceOf(Directory::class));
    }

    /** @test */
    public function it can return a file()
    {
        $file = (new LocalDirectory('foo'))->file('bar');
        assertThat($file, isInstanceOf(LocalFile::class));
    }

    /** @test */
    public function it return true when the directory exist()
    {
        $directory = new LocalDirectory($this->getDir()->url());

        assertTrue($directory->exists());
    }

    /** @test */
    public function it return false when the directory does not exist()
    {
        $directory = new LocalDirectory($this->getDir()->url() . '/foo');

        assertFalse($directory->exists());
    }

    /** @test */
    public function it add the file scheme in the uri if not provided()
    {
        $directory = new LocalDirectory('foo');

        assertThat($directory->uri(), equalTo('file://foo'));
    }

    /** @test */
    public function it can return the path of a directory()
    {
        $directory = (new LocalDirectory('foo'))->directory('bar');

        assertThat($directory->path(), equalTo('foo/bar'));
    }

    /** @test */
    public function it can return a directory()
    {
        $directory = new LocalDirectory('foo');

        assertThat($directory->directory('bar'), isInstanceOf(LocalDirectory::class));
    }

    /** @test */
    public function it can create a directory()
    {
        $directory = new LocalDirectory(($streamDir = $this->getDir())->url());

        $newDirectory = $directory->directory('new');
        $newDirectory->create();

        assertThat($streamDir->hasChild('new'), isTrue());
    }

    /** @test */
    public function it cannot create a directory when the root directory is not writable()
    {
        $this->expectException(UnableToCreateDirectory::class);
        $this->expectExceptionMessageRegExp('/^Unable to create directory/');

        $directory = new LocalDirectory($this->getLockedDir()->url());
        $directory->directory('new')->create();
    }

    private function getDir(): vfsStreamDirectory
    {
        return vfsStream::setup('root');
    }

    private function getLockedDir(): vfsStreamDirectory
    {
        return vfsStream::setup('root', 0555);
    }
}