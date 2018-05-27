<?php
namespace ElevenLabs\DockerHostManager\EventDispatcher;

class EventType
{
    public const EVENT_STANDARD  = 'standard';
    public const EVENT_ERROR     = 'error';
    public const EVENT_INTERNAL  = 'internal';
    private const EVENT_TYPES    = [self::EVENT_STANDARD, self::EVENT_ERROR, self::EVENT_INTERNAL];

    private $type;

    public function __construct(string $type)
    {
        if (!\in_array($type, self::EVENT_TYPES, true)) {
            throw new \InvalidArgumentException(
                $type . ' is not a valid event type. Supported: ' . implode(', ', self::EVENT_TYPES)
            );
        }
        $this->type = $type;
    }

    public function __toString(): string
    {
        return $this->type;
    }
}