<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\File;

class LocalDirectory implements Directory
{
    private $dirname;

    public function __construct($dirname)
    {
        $schemeSeparator = '://';
        if (strpos($dirname, $schemeSeparator) === false) {
            $dirname = 'file://' . $dirname;
        }

        $this->dirname = $dirname;
    }

    public static function get(string $path): Directory
    {
        return new self($path);
    }

    public function file(string $path): File
    {
        return LocalFile::get($this->uri() . '/' . $path);
    }

    public function exists(): bool
    {
        return is_dir($this->dirname);
    }

    public function uri(): string
    {
        return $this->dirname;
    }
}
