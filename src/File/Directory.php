<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\File;

interface Directory
{
    /**
     * Return a directory instance.
     *
     * @param string $path
     *
     * @return Directory
     */
    public static function get(string $path): self;

    /**
     * Get a file within the directory.
     *
     * @param string $path
     *
     * @return File
     */
    public function file(string $path): File;

    /**
     * Get a directory within the directory.
     *
     * @param string $path
     * @return Directory
     */
    public function directory(string $path): Directory;

    /**
     * Check if a directory exist.
     *
     * @return bool
     */
    public function exists(): bool;

    /**
     * Create the directory
     */
    public function create(): void;

    /**
     * Return the URI of the directory.
     *
     * @return string
     */
    public function uri(): string;

    /**
     * Return the path of a directory
     * @return string
     */
    public function path(): string;
}
