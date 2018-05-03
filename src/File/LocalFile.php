<?php declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\File;

use ElevenLabs\DockerHostManager\File\Exception\FileDoesNotExist;
use ElevenLabs\DockerHostManager\File\Exception\FileNotWritable;

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
    public function exists(): bool
    {
        return file_exists($this->filename);
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        if (!$this->exists()) {
            throw new FileDoesNotExist('Could not find file at ' . $this->filename);
        }

        return file_get_contents($this->filename);
    }

    /**
     * {@inheritdoc}
     */
    public function putContents(string $contents): void
    {
        if (!$this->exists()) {
            throw new FileDoesNotExist('Could not find file at ' . $this->filename);
        }
        if (@file_put_contents($this->filename, $contents) === false) {
            throw new FileNotWritable('Could not write in file ' . $this->filename);
        }
    }

}
