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
    /** @test */
    public function it_implements_file()
    {
        assertThat(new LocalFile('foo'), isInstanceOf(File::class));
    }

    /** @test */
    public function it_read_the_content_of_a_file()
    {
        $filename  = $this->addFile('foo', 'some content')->url();
        $localFile = new LocalFile($filename);

        assertThat($localFile->read(), equalTo('some content'));
    }

    /** @test */
    public function it_can_update_the_content_of_a_file()
    {
        $filename  = $this->addFile('foo')->url();
        $localFile = new LocalFile($filename);
        $localFile->put('new content');

        assertThat($localFile->read(), equalTo('new content'));
    }

    /** @test */
    public function it_return_true_if_a_file_exists()
    {
        $filename  = $this->addFile('foo')->url();
        $localFile = new LocalFile($filename);

        assertTrue($localFile->exists());
    }

    /** @test */
    public function it_return_false_when_a_file_does_not_exist()
    {
        $filename  = $this->getDir()->url() . '/foo';
        $localFile = new LocalFile($filename);

        assertFalse($localFile->exists());
    }

    /** @test */
    public function it_throw_an_exception_when_trying_to_get_the_content_of_the_file_which_do_not_exist()
    {
        $this->expectException(FileDoesNotExist::class);

        $filename = $this->getDir()->url() . '/foo';

        (new LocalFile($filename))->read();
    }

    /** @test */
    public function it_throw_an_exception_when_trying_to_update_the_content_of_a_file_which_is_not_writable()
    {
        $this->expectException(CouldNotWriteFile::class);

        $filename = $this->getLockedDir()->url() . '/foo';

        $localFile = new LocalFile($filename);
        $localFile->put('new content');
    }

    /** @test */
    public function it_can_create_a_file_in_a_directory()
    {
        $file = new LocalFile($this->getDir()->url() . '/foo/bar.txt');
        $file->put('hello');

        assertThat($file->read(), equalTo('hello'));
    }

    /** @test */
    public function it_throw_an_exception_when_trying_to_create_a_file_in_a_directory_that_is_not_writable()
    {
        $this->expectException(CouldNotWriteFile::class);
        $this->expectExceptionMessage('Unable to create file in vfs://root/foo');

        $filename = $this->getLockedDir()->url() . '/foo/bar.txt';

        $file = new LocalFile($filename);
        $file->put('');
    }

    /** @test */
    public function it_can_be_constructed_statically()
    {
        $localFile = LocalFile::get('hello.txt');

        assertThat($localFile, isInstanceOf(LocalFile::class));
    }

    /** @test */
    public function it_add_the_file_scheme_into_the_filename_if_not_provided()
    {
        $localFile = new LocalFile('foo');
        assertThat($localFile->uri(), equalTo('file://foo'));
    }

    /** @test */
    public function it_can_delete_a_file()
    {
        $dir = $this->getDir();

        $file = new LocalFile($dir->url() . '/foo');
        $file->put('');
        $file->delete();

        assertFalse($dir->hasChild('foo'));
    }

    /** @test */
    public function it_can_return_the_path_of_a_file()
    {
        $file = new LocalFile('file:///foo/bar.txt');

        assertThat($file->path(), equalTo('/foo/bar.txt'));
    }

    private function getDir(): vfsStreamDirectory
    {
        return vfsStream::setup('root', 0755);
    }

    private function getLockedDir(): vfsStreamDirectory
    {
        return vfsStream::setup('root', 0555);
    }

    private function addFile($name, $content = '', $permission = 0644): vfsStreamFile
    {
        $file = vfsStream::newFile($name, $permission)->withContent($content);
        $this->getDir()->addChild($file);

        return $file;
    }
}