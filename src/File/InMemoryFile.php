<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\File;

use ElevenLabs\DockerHostManager\File\Exception\FileDoesNotExist;
use ElevenLabs\DockerHostManager\File\Exception\CouldNotWriteFile;

class InMemoryFile implements FileHandler
{
    /** @var bool */
    private $exists;
    /** @var string */
    private $contents;
    /** @var bool */
    private $writable;

    public function __construct(string $contents = '', bool $shouldExist = true, bool $isWritable = true)
    {
        $this->contents = $contents;
        $this->exists   = $shouldExist;
        $this->writable = $isWritable;
    }

    public static function getFile(string $path): FileHandler
    {
        return new self();
    }

    /**
     * {@inheritdoc}
     */
    public function exists(): bool
    {
        return $this->exists;
    }

    /**
     * {@inheritdoc}
     */
    public function read(): string
    {
        if (!$this->exists()) {
            throw new FileDoesNotExist('Could not find file');
        }
        return $this->contents;
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $contents): void
    {
        if (!$this->exists()) {
            throw new FileDoesNotExist('Could not find file');
        }
        if ($this->writable === false) {
            throw new CouldNotWriteFile('Could not write in file');
        }
        $this->contents = $contents;
    }

}
