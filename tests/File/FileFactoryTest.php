<?php
/**
 * Created by PhpStorm.
 * User: gcanal
 * Date: 25/05/18
 * Time: 11:25
 */

namespace ElevenLabs\DockerHostManager\File;

use PHPUnit\Framework\TestCase;

class FileFactoryTest extends TestCase
{
    /** @test */
    public function it return an instance of a file handler implementation by providing a filehandler classname()
    {
        $fileFactory = new FileFactory(InMemoryFile::class);
        $fileHandler = $fileFactory->getFile('hello.txt');

        assertThat($fileHandler, isInstanceOf(InMemoryFile::class));
    }

    /** @test */
    public function it throw an exception when a given classname does not implements the filehandler interface()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('ArrayObject does not implements the ' . FileHandler::class . ' interface');

        new FileFactory(\ArrayObject::class);
    }

    /** @test */
    public function it accept a file prefix()
    {
        $fileFactory = new FileFactory(FileHandlerSpy::class, 'file:///root');

        /** @var FileHandlerSpy $fileHandler */
        $fileHandler = $fileFactory->getFile('hello.txt');

        assertThat($fileHandler, isInstanceOf(FileHandlerSpy::class));
        assertThat($fileHandler::$filePath, equalTo('file:///root/hello.txt'));
    }
}

class FileHandlerSpy implements FileHandler
{
    public static $filePath;

    public static function getFile(string $path): FileHandler
    {
       self::$filePath = $path;

       return new self;
    }

    public function exists(): bool
    {
        return true;
    }

    public function read(): string
    {
        return '';
    }

    public function put(string $contents): void
    {
    }
}
