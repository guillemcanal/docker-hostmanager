<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\File;

use ElevenLabs\DockerHostManager\File\Exception\FileDoesNotExist;
use ElevenLabs\DockerHostManager\File\Exception\FileNotWritable;

interface FileHandler
{
    /**
     * Check if a file exists
     *
     * @return bool Either if a file exist or not
     */
    public function exists(): bool;

    /**
     * Return the content of a file
     *
     * @return  string A file content
     *
     * @throws FileDoesNotExist When the file does not exists
     */
    public function getContents(): string;

    /**
     * Write a string to a file
     *
     * @param string $contents
     *
     * @throws FileDoesNotExist When the file does not exists
     * @throws FileNotWritable  When the file is not writable
     */
    public function putContents(string $contents): void;
}