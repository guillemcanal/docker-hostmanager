<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\File;

use ElevenLabs\DockerHostManager\File\Exception\FileDoesNotExist;
use ElevenLabs\DockerHostManager\File\Exception\CouldNotWriteFile;
use ElevenLabs\DockerHostManager\File\Exception\UnableToCreateFile;

class LocalFile implements FileHandler
{
    /** @var string */
    private $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * {@inheritdoc}
     */
    public static function getFile(string $path): FileHandler
    {
        return new self($path);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(): bool
    {
        return file_exists($this->filename);
    }

    /**
     * {@inheritdoc}
     */
    public function read(): string
    {
        if (!$this->exists()) {
            throw new FileDoesNotExist('Could not find file at ' . $this->filename);
        }

        return file_get_contents($this->filename);
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $contents): void
    {
        if (!$this->exists()) {
            $this->createFileDirectory();
        }
        if (@file_put_contents($this->filename, $contents) === false) {
            throw new CouldNotWriteFile('Could not write in file ' . $this->filename);
        }
    }

    private function createFileDirectory(): void
    {
        $dirname = \dirname($this->filename);
        if (is_dir($dirname)) {
            return;
        }

        if (!mkdir($dirname, 0755, true) && !is_dir($dirname)) {
            throw new CouldNotWriteFile('Unable to create file in ' . $dirname);
        }
    }

}
