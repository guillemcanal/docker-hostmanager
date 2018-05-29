<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\File;

use ElevenLabs\DockerHostManager\File\Exception\UnableToCreateDirectory;

class LocalDirectory implements Directory
{
    private $dirname;

    public function __construct($dirname)
    {
        $schemeSeparator = '://';
        if (false === \strpos($dirname, $schemeSeparator)) {
            $dirname = 'file://'.$dirname;
        }

        $this->dirname = $dirname;
    }

    public static function get(string $path): Directory
    {
        return new self($path);
    }

    public function file(string $path): File
    {
        return LocalFile::get($this->uri().'/'.$path);
    }

    public function directory(string $path): Directory
    {
        return self::get($this->uri().'/'.$path);
    }

    public function create(): void
    {
        if (!\mkdir($this->dirname) && !\is_dir($this->dirname)) {
            throw new UnableToCreateDirectory('Unable to create directory '.$this->uri());
        }
    }

    public function exists(): bool
    {
        return \is_dir($this->dirname);
    }

    public function uri(): string
    {
        return $this->dirname;
    }

    public function path(): string
    {
        return \parse_url($this->uri())['path'];
    }
}
