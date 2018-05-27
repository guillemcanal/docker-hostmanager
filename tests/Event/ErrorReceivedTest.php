<?php
namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;
use PHPUnit\Framework\TestCase;

class ErrorReceivedTest extends TestCase
{
    /** @test */
    public function it implements the event interface()
    {
        $event = new ErrorReceived('test', new \Exception('error'));
        assertThat($event, isInstanceOf(Event::class));
    }

    /** @test */
    public function it provide a name()
    {
        $event = new ErrorReceived('test', new \Exception('error'));
        assertThat($event->getName(), equalTo('error.received'));
    }

    /** @test */
    public function it provide a type()
    {
        $event = new ErrorReceived('test', new \Exception('error'));
        assertThat($event->getType(), equalTo(new EventType(EventType::EVENT_ERROR)));
    }

    /** @test */
    public function it can be transformed into an array()
    {
        $event = new ErrorReceived('test', new \Exception('error'));
        assertThat(
            $event->toArray(),
            equalTo(
                [
                    'message' => 'test',
                    'exception' => [
                        'name' => 'Exception',
                        'message' => 'error'
                    ]
                ]
            )
        );
    }
}
