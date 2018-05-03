<?php

namespace ElevenLabs\DockerHostManager\File;

use ElevenLabs\DockerHostManager\File\Exception\FileDoesNotExist;
use ElevenLabs\DockerHostManager\File\Exception\FileNotWritable;
use PHPUnit\Framework\TestCase;

class InMemoryFileTest extends TestCase
{
    /** @test */
    public function it provide the content for a file()
    {
        $file = new InMemoryFile('some content');

        assertThat($file->getContents(), equalTo('some content'));
    }

    /** @test */
    public function it can update the content of a file()
    {
        $file = new InMemoryFile('some content');
        $file->putContents('new content');

        assertThat($file->getContents(), equalTo('new content'));
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
        $file = new InMemoryFile('', $souldExist = false);

        assertFalse($file->exists());
    }

    /** @test */
    public function it throw an exception when trying to get the content of the file which dont exist()
    {
        $this->expectException(FileDoesNotExist::class);

        $file = new InMemoryFile('', $souldExist = false);
        $file->getContents();
    }

    /** @test */
    public function it throw an exception when trying to update the content of the file which dont exist()
    {
        $this->expectException(FileDoesNotExist::class);

        $file = new InMemoryFile('', $souldExist = false);
        $file->putContents('new content');
    }

    /** @test */
    public function it throw an exception when trying to update the content of the file which is not writable()
    {
        $this->expectException(FileNotWritable::class);

        $file = new InMemoryFile('', $souldExist = true, $isWritable = false);
        $file->putContents('new content');
    }

}