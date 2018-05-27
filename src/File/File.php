<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\File;

use ElevenLabs\DockerHostManager\File\Exception\FileDoesNotExist;
use ElevenLabs\DockerHostManager\File\Exception\CouldNotWriteFile;
use ElevenLabs\DockerHostManager\File\Exception\UnableToCreateFile;
use Http\Message\UriFactory;
use Psr\Http\Message\UriInterface;

interface File
{
    /**
     * Create a file at a given path
     *
     * @param string $path
     *
     * @return File
     */
    public static function get(string $path): File;

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
     * @param string $contents Some file content
     *
     * @throws FileDoesNotExist   When the file does not exists
     * @throws CouldNotWriteFile  When the file is not writable
     */
    public function put(string $contents): void;

    /**
     * Return the URI of the file
     *
     * @return string A file URI (ex: file:///etc/hosts)
     */
    public function uri(): string;

    /**
     * Delete the file
     */
    public function delete(): void;
}