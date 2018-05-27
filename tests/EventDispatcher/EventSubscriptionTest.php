<?php

namespace ElevenLabs\DockerHostManager\EventDispatcher;

use PHPUnit\Framework\TestCase;

class EventSubscriptionTest extends TestCase
{
    /** @test */
    public function it should call a given callback()
    {
        $callbackHasBeenCalled = false;
        $subscription = new EventSubscription(
            DummyEvent::class,
            function () use (&$callbackHasBeenCalled) {
                $callbackHasBeenCalled = true;
            }
        );

        $subscription->handle(new DummyEvent());

        assertTrue($callbackHasBeenCalled);
    }

    /** @test */
    public function it throw an exception when a given event classname does not exist()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ArrayObject does not implements ' . Event::class);

        new EventSubscription(\ArrayObject::class, function () {});
    }
}

class DummyEvent implements Event
{
    public function getName(): string
    {
        return 'dummy';
    }

    public function toArray(): array
    {
        return [];
    }

    public function getType(): EventType
    {
        return new EventType(EventType::EVENT_STANDARD);
    }
}