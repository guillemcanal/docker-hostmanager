<?php

namespace ElevenLabs\DockerHostManager\EventDispatcher;

use PHPUnit\Framework\TestCase;

class EventTypeTest extends TestCase
{
    /**
     * @test
     * @dataProvider  getValidEventType
     */
    public function it can be(string $eventTypeString)
    {
        $eventType = new EventType($eventTypeString);

        assertThat((string) $eventType, equalTo($eventTypeString));
    }

    public function getValidEventType(): array
    {
        return [
            'a standard event type'  => [EventType::EVENT_STANDARD],
            'an error event type'    => [EventType::EVENT_ERROR],
            'an internal event type' => [EventType::EVENT_INTERNAL],
        ];
    }

    /** @test */
    public function it throw and exception when the given event type is not suported()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/^hello is not a valid event type./');

        new EventType('hello');
    }
}