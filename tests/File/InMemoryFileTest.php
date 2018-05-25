<?php

namespace ElevenLabs\DockerHostManager\File;

use ElevenLabs\DockerHostManager\File\Exception\FileDoesNotExist;
use ElevenLabs\DockerHostManager\File\Exception\CouldNotWriteFile;
use PHPUnit\Framework\TestCase;

class InMemoryFileTest extends TestCase
{
    /** @test */
    public function it read the content of a file()
    {
        $file = new InMemoryFile('some content');

        assertThat($file->read(), equalTo('some content'));
    }

    /** @test */
    public function it can update the content of a file()
    {
        $file = new InMemoryFile('some content');
        $file->put('new content');

        assertThat($file->read(), equalTo('new content'));
    }

    /** @test */
    public function it return true if a file exists()
    {
        $file = new InMemoryFile();

        assertTrue($file->exists());
    }

    /** @test */
    public function it return false when a file does not exist()
    {
        $file = new InMemoryFile('', $shouldExist = false);

        assertFalse($file->exists());
    }

    /** @test */
    public function it throw an exception when trying to get the content of the file which do not exist()
    {
        $this->expectException(FileDoesNotExist::class);

        $file = new InMemoryFile('', $shouldExist = false);
        $file->read();
    }

    /** @test */
    public function it throw an exception when trying to update the content of the file which do not exist()
    {
        $this->expectException(FileDoesNotExist::class);

        $file = new InMemoryFile('', $shouldExist = false);
        $file->put('new content');
    }

    /** @test */
    public function it throw an exception when trying to update the content of a file which is not writable()
    {
        $this->expectException(CouldNotWriteFile::class);

        $file = new InMemoryFile('', $shouldExist = true, $isWritable = false);
        $file->put('new content');
    }

}