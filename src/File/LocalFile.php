<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\File;

use ElevenLabs\DockerHostManager\File\Exception\CouldNotWriteFile;
use ElevenLabs\DockerHostManager\File\Exception\FileDoesNotExist;

class LocalFile implements File
{
    /** @var string */
    private $filename;

    public function __construct(string $filename)
    {
        $schemeSeparator = '://';
        if (false === \strpos($filename, $schemeSeparator)) {
            $filename = 'file://'.$filename;
        }
        $this->filename = $filename;
    }

    /**
     * {@inheritdoc}
     */
    public static function get(string $path): File
    {
        return new self($path);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(): bool
    {
        return \file_exists($this->filename);
    }

    /**
     * {@inheritdoc}
     */
    public function read(): string
    {
        if (!$this->exists()) {
            throw new FileDoesNotExist('Could not find file at '.$this->filename);
        }

        return \file_get_contents($this->filename);
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $contents): void
    {
        $this->ensureFileDirectory();
        if (false === @\file_put_contents($this->filename, $contents)) {
            throw new CouldNotWriteFile('Could not write in file '.$this->filename);
        }
    }

    private function ensureFileDirectory(): void
    {
        $dirname = \dirname($this->filename);
        if (\is_dir($dirname)) {
            return;
        }
        if (!\mkdir($dirname, 0755, true) && !\is_dir($dirname)) {
            throw new CouldNotWriteFile('Unable to create file in '.$dirname);
        }
    }

    public function uri(): string
    {
        return $this->filename;
    }

    public function path(): string
    {
        $parts = \explode('://', $this->uri());

        return \end($parts);
    }

    public function delete(): void
    {
        \unlink($this->filename);
    }
}
