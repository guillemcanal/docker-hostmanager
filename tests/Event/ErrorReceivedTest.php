<?php
namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;
use PHPUnit\Framework\TestCase;

class ErrorReceivedTest extends TestCase
{
    /** @test */
    public function it_implements_the_event_interface()
    {
        $event = new ErrorReceived('test', new \Exception('error'));
        assertThat($event, isInstanceOf(Event::class));
    }

    /** @test */
    public function it_provide_a_name()
    {
        $event = new ErrorReceived('test', new \Exception('error'));
        assertThat($event->getName(), equalTo('error.received'));
    }

    /** @test */
    public function it_provide_a_type()
    {
        $event = new ErrorReceived('test', new \Exception('error'));
        assertThat($event->getType(), equalTo(new EventType(EventType::EVENT_ERROR)));
    }

    /** @test */
    public function it_can_be_transformed_into_an_array()
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
