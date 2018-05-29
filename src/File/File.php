<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\File;

use ElevenLabs\DockerHostManager\File\Exception\CouldNotWriteFile;
use ElevenLabs\DockerHostManager\File\Exception\FileDoesNotExist;

interface File
{
    /**
     * Create a file at a given path.
     *
     * @param string $path
     *
     * @return File
     */
    public static function get(string $path): self;

    /**
     * Check if a file exists.
     *
     * @return bool Either if a file exist or not
     */
    public function exists(): bool;

    /**
     * Read the content of a file.
     *
     * @throws FileDoesNotExist When the file does not exists
     *
     * @return string A file content
     */
    public function read(): string;

    /**
     * Put content in a file.
     *
     * @param string $contents Some file content
     *
     * @throws FileDoesNotExist  When the file does not exists
     * @throws CouldNotWriteFile When the file is not writable
     */
    public function put(string $contents): void;

    /**
     * Return the URI of the file.
     *
     * @return string A file URI (ex: file:///etc/hosts)
     */
    public function uri(): string;

    /**
     * Return the path of a file
     * @return string
     */
    public function path(): string;

    /**
     * Delete the file.
     */
    public function delete(): void;
}
