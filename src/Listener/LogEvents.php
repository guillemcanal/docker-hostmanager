<?php
namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LogEvents implements EventListener
{
    private $logger;

    private const EVENT_LOG_LEVELS = [
        EventType::EVENT_STANDARD => LogLevel::INFO,
        EventType::EVENT_ERROR => LogLevel::ERROR,
        EventType::EVENT_INTERNAL => LogLevel::DEBUG
    ];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function subscription(): EventSubscription
    {
        return new EventSubscription(
            Event::class,
            function (Event $event) {
                $this->logEvent($event);
            }
        );
    }

    private function logEvent(Event $event): void
    {
        $this->logger->log($this->logLevel($event->getType()),  $event->getName(), $event->toArray());
    }

    private function logLevel(EventType $eventType): string
    {
        return self::EVENT_LOG_LEVELS[(string) $eventType];
    }
}
