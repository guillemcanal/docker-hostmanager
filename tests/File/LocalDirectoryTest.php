<?php

namespace ElevenLabs\DockerHostManager\File;

use ElevenLabs\DockerHostManager\File\Exception\UnableToCreateDirectory;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class LocalDirectoryTest extends TestCase
{
    /** @test */
    public function it_implements_the_directory_interface()
    {
        assertThat(new LocalDirectory('foo'), isInstanceOf(Directory::class));
    }

    /** @test */
    public function it_can_be_constructed_statically()
    {
        assertThat(LocalDirectory::get('foo'), isInstanceOf(Directory::class));
    }

    /** @test */
    public function it_can_return_a_file()
    {
        $file = (new LocalDirectory('foo'))->file('bar');
        assertThat($file, isInstanceOf(LocalFile::class));
    }

    /** @test */
    public function it_return_true_when_the_directory_exist()
    {
        $directory = new LocalDirectory($this->getDir()->url());

        assertTrue($directory->exists());
    }

    /** @test */
    public function it_return_false_when_the_directory_does_not_exist()
    {
        $directory = new LocalDirectory($this->getDir()->url() . '/foo');

        assertFalse($directory->exists());
    }

    /** @test */
    public function it_add_the_file_scheme_in_the_uri_if_not_provided()
    {
        $directory = new LocalDirectory('foo');

        assertThat($directory->uri(), equalTo('file://foo'));
    }

    /** @test */
    public function it_can_return_the_path_of_a_directory()
    {
        $directory = (new LocalDirectory('foo'))->directory('bar');

        assertThat($directory->path(), equalTo('foo/bar'));
    }

    /** @test */
    public function it_can_return_a_directory()
    {
        $directory = new LocalDirectory('foo');

        assertThat($directory->directory('bar'), isInstanceOf(LocalDirectory::class));
    }

    /** @test */
    public function it_can_create_a_directory()
    {
        $directory = new LocalDirectory(($streamDir = $this->getDir())->url());

        $newDirectory = $directory->directory('new');
        $newDirectory->create();

        assertThat($streamDir->hasChild('new'), isTrue());
    }

    /** @test */
    public function it_cannot_create_a_directory_when_the_root_directory_is_not_writable()
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