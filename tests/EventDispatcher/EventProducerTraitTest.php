<?php

namespace ElevenLabs\DockerHostManager\EventDispatcher;

use PHPUnit\Framework\TestCase;

class EventProducerTraitTest extends TestCase
{
    /** @test */
    public function it_throw_an_exception_when_a_produced_event_is_not_an_object()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Produced event should be of type object. Got string');

        $eventProducer = new class {
            use EventProducerTrait;
            public function test()
            {
                $this->produceEvent('invalid');
            }
        };

        $eventProducer->test();
    }
}
