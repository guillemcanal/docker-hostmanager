<?php

namespace ElevenLabs\DockerHostManager\EventDispatcher;

use PHPUnit\Framework\TestCase;

class EventSubscriptionTest extends TestCase
{
    /** @test */
    public function it_should_call_a_given_callback()
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
    public function it_throw_an_exception_when_a_given_event_classname_does_not_exist()
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