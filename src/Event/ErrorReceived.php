<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;

class ErrorReceived implements Event
{
    private $message;
    private $exception;

    public function getType(): EventType
    {
        return new EventType(EventType::EVENT_ERROR);
    }

    public function getName(): string
    {
        return 'error.received';
    }

    public function toArray(): array
    {
        return [
            'message'   => $this->getMessage(),
            'exception' => [
                'name'    => \get_class($this->exception),
                'message' => $this->exception->getMessage()
            ]
        ];
    }

    public function __construct($message, \Exception $e)
    {
        $this->message   = $message;
        $this->exception = $e;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getException(): \Exception
    {
        return $this->exception;
    }
}