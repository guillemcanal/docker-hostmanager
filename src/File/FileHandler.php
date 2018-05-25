<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\File;

use ElevenLabs\DockerHostManager\File\Exception\FileDoesNotExist;
use ElevenLabs\DockerHostManager\File\Exception\CouldNotWriteFile;
use ElevenLabs\DockerHostManager\File\Exception\UnableToCreateFile;

interface FileHandler
{
    /**
     * Create a file at a given path
     *
     * @param string $path
     *
     * @return FileHandler
     */
    public static function getFile(string $path): FileHandler;

    /**
     * Check if a file exists
     *
     * @return bool Either if a file exist or not
     */
    public function exists(): bool;

    /**
     * Read the content of a file
     *
     * @return  string A file content
     *
     * @throws FileDoesNotExist When the file does not exists
     */
    public function read(): string;

    /**
     * Put content in a file
     *
     * @param string $contents
     *
     * @throws FileDoesNotExist   When the file does not exists
     * @throws CouldNotWriteFile  When the file is not writable
     */
    public function put(string $contents): void;
}