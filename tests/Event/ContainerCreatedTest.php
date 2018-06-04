<?php
namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;
use PHPUnit\Framework\TestCase;

class ContainerCreatedTest extends TestCase
{
    /** @test */
    public function it_implements_the_event_interface()
    {
        $event = new ContainerCreated('', [], []);
        assertThat($event, isInstanceOf(Event::class));
    }

    /** @test */
    public function it_provide_a_name()
    {
        $event = new ContainerCreated('', [], []);
        assertThat($event->getName(), equalTo('container.created'));
    }

    /** @test */
    public function it_provide_a_type()
    {
        $event = new ContainerCreated('', [], []);
        assertThat($event->getType(), equalTo(new EventType(EventType::EVENT_STANDARD)));
    }

    /** @test */
    public function it_can_be_transformed_into_an_array()
    {
        $event = new ContainerCreated('test', ['foo.domain.fr'], ['foo' => 'bar']);
        assertThat(
            $event->toArray(),
            equalTo(
                [
                    'containerName'       => 'test',
                    'containerAttributes' => ['foo' => 'bar'],
                    'domainNames'         => ['foo.domain.fr'],
                ]
            )
        );
    }
}
