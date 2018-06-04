<?php

namespace ElevenLabs\DockerHostManager\EventDispatcher;

use PHPUnit\Framework\TestCase;

class EventDispatcherTest extends TestCase
{
    /** @test */
    public function it_can_dispatch_an_event_to_an_event_listener()
    {
        $handledEvent = false;
        $subscription = new EventSubscription(
            DummyEventFoo::class,
            function (DummyEventFoo $event) use (&$handledEvent) {
                $handledEvent = $event;
            }
        );

        $listener = $this->prophesize(EventListener::class);
        $listener->subscription()->willReturn($subscription);

        $dispatcher = new EventDispatcher($listener->reveal());
        $dispatcher->dispatch($dispatchedEvent = new DummyEventFoo());

        assertThat($handledEvent, equalTo($dispatchedEvent));
    }

    /** @test */
    public function it_only_dispatch_object()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The given event should be an object. Got string');

        $dispatcher = new EventDispatcher();
        $dispatcher->dispatch('');
    }

    /** @test */
    public function it_does_not_dispatch_an_event_is_no_listener_subscribed_to_it()
    {
        $handledEvent = null;
        $subscription = new EventSubscription(
            DummyEventFoo::class,
            function ($event) use (&$handledEvent) {
                $handledEvent = $event;
            }
        );

        $listener = $this->prophesize(EventListener::class);
        $listener->subscription()->willReturn($subscription);

        $dispatcher = new EventDispatcher($listener->reveal());
        $dispatcher->dispatch($dispatchedEvent = new DummyEventBar());

        assertThat($handledEvent, isNull());
    }

    /** @test */
    public function it_collect_and_dispatch_events_from_event_producers()
    {
        $producedEvent =new DummyEventBar();

        $eventProducer = $this->prophesize(EventListener::class);
        $eventProducer->willImplement(EventProducer::class);
        $eventProducer->producedEvents()->willReturn([$producedEvent]);
        $eventProducer->subscription()->willReturn(
            new EventSubscription(
                DummyEventFoo::class,
                function (DummyEventFoo $event) {}
            )
        );

        $handledEvent = null;
        $eventListener = $this->prophesize(EventListener::class);
        $eventListener->subscription()->willReturn(
            new EventSubscription(
                DummyEventBar::class,
                function (DummyEventBar $event) use (&$handledEvent) {
                    $handledEvent = $event;
                }
            )
        );

        $dispatcher = new EventDispatcher($eventProducer->reveal(), $eventListener->reveal());
        $dispatcher->dispatch(new DummyEventFoo());

        assertThat($handledEvent, equalTo($producedEvent));
    }
}

class DummyEventFoo implements Event
{
    public function getName(): string
    {
        return 'foo';
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

class DummyEventBar implements Event
{
    public function getName(): string
    {
        return 'bar';
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
