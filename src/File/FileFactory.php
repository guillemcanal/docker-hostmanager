<?php

namespace ElevenLabs\DockerHostManager\File;

use GuzzleHttp\Psr7\Uri;

class FileFactory
{
    /** @var Uri */
    private $prefix;
    /** @var string */
    private $fileHandlerImpl;

    public function __construct($fileHandlerImpl, string $prefix = 'file:')
    {
        if (!\is_a($fileHandlerImpl, FileHandler::class, true)) {
            throw new \UnexpectedValueException($fileHandlerImpl . ' must implements ' . FileHandler::class);
        }

        $this->prefix = new Uri($prefix);
        $this->fileHandlerImpl = $fileHandlerImpl;
    }

    public function getFile(string $path): FileHandler
    {
        $uri = $this->prefix->withPath($this->prefix->getPath() . '/' . ltrim($path, '/'));

        return forward_static_call([$this->fileHandlerImpl, 'getFile'], (string) $uri);
    }
}