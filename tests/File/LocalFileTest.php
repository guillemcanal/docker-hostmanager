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
    public function it implements file()
    {
        assertThat(new LocalFile('foo'), isInstanceOf(File::class));
    }

    /** @test */
    public function it read the content of a file()
    {
        $filename  = $this->addFile('foo', 'some content')->url();
        $localFile = new LocalFile($filename);

        assertThat($localFile->read(), equalTo('some content'));
    }

    /** @test */
    public function it can update the content of a file()
    {
        $filename  = $this->addFile('foo')->url();
        $localFile = new LocalFile($filename);
        $localFile->put('new content');

        assertThat($localFile->read(), equalTo('new content'));
    }

    /** @test */
    public function it return true if a file exists()
    {
        $filename  = $this->addFile('foo')->url();
        $localFile = new LocalFile($filename);

        assertTrue($localFile->exists());
    }

    /** @test */
    public function it return false when a file does not exist()
    {
        $filename  = $this->getDir()->url() . '/foo';
        $localFile = new LocalFile($filename);

        assertFalse($localFile->exists());
    }

    /** @test */
    public function it throw an exception when trying to get the content of the file which do not exist()
    {
        $this->expectException(FileDoesNotExist::class);

        $filename = $this->getDir()->url() . '/foo';

        (new LocalFile($filename))->read();
    }

    /** @test */
    public function it throw an exception when trying to update the content of a file which is not writable()
    {
        $this->expectException(CouldNotWriteFile::class);

        $filename = $this->getLockedDir()->url() . '/foo';

        $localFile = new LocalFile($filename);
        $localFile->put('new content');
    }

    /** @test */
    public function it can create a file in a directory()
    {
        $file = new LocalFile($this->getDir()->url() . '/foo/bar.txt');
        $file->put('hello');

        assertThat($file->read(), equalTo('hello'));
    }

    /** @test */
    public function it throw an exception when trying to create a file in a directory that is not writable()
    {
        $this->expectException(CouldNotWriteFile::class);
        $this->expectExceptionMessage('Unable to create file in vfs://root/foo');

        $filename = $this->getLockedDir()->url() . '/foo/bar.txt';

        $file = new LocalFile($filename);
        $file->put('');
    }

    /** @test */
    public function it can be constructed statically()
    {
        $localFile = LocalFile::get('hello.txt');

        assertThat($localFile, isInstanceOf(LocalFile::class));
    }

    /** @test */
    public function it add the file scheme into the filename if not provided()
    {
        $localFile = new LocalFile('foo');
        assertThat($localFile->uri(), equalTo('file://foo'));
    }

    /** @test */
    public function it can delete a file()
    {
        $dir = $this->getDir();

        $file = new LocalFile($dir->url() . '/foo');
        $file->put('');
        $file->delete();

        assertFalse($dir->hasChild('foo'));
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