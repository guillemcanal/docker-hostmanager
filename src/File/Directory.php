<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\File;

interface Directory
{
    /**
     * Return a directory instance
     *
     * @param string $path
     *
     * @return Directory
     */
    public static function get(string $path): Directory;

    /**
     * Get a file within the directory
     *
     * @param string $path
     *
     * @return File
     */
    public function file(string $path): File;

    /**
     * Check if a directory exist
     *
     * @return bool
     */
    public function exists(): bool;

    /**
     * Return the URI of the directory
     *
     * @return string
     */
    public function uri(): string;
}