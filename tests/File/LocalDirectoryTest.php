<?php

namespace ElevenLabs\DockerHostManager\File;

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

    private function getDir(): vfsStreamDirectory
    {
        return vfsStream::setup('root');
    }
}