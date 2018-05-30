<?php

namespace ElevenLabs\DockerHostManager\EventDispatcher;

use PHPUnit\Framework\TestCase;

class EventProducerTraitTest extends TestCase
{
    /** @test */
    public function it throw an exception when a produced event is not an object()
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
