<?php

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\EventsGetResponse200;
use Docker\API\Model\EventsGetResponse200Actor;
use ElevenLabs\DockerHostManager\HostsFileManager;
use ElevenLabs\DockerHostManager\DomainNameExtractor\DomainNameExtractor;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class UpdateHostsFileTest extends TestCase
{
    /**
     * @test
     * @dataProvider getSupportedEvents
     */
    public function it support(EventsGetResponse200 $event)
    {
        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $domainNameExtractor = $this->prophesize(DomainNameExtractor::class);

        $listener = new UpdateHostsFile(
            $hostsFileManager->reveal(),
            $domainNameExtractor->reveal()
        );

        assertTrue($listener->support($event));
    }

    public function getSupportedEvents(): array
    {
        return [
            'docker container create event' => [
                (new EventsGetResponse200())->setType('container')->setAction('create')
            ],
            'docker container destroy event' => [
                (new EventsGetResponse200())->setType('container')->setAction('destroy')
            ],
        ];
    }

    /** @test */
    public function it ignore an event without an actor()
    {
        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $domainNameExtractor = $this->prophesize(DomainNameExtractor::class);
        $domainNameExtractor->provideDomainNames(Argument::any())->shouldNotBeCalled();

        $listener = new UpdateHostsFile(
            $hostsFileManager->reveal(),
            $domainNameExtractor->reveal()
        );

        $event = (new EventsGetResponse200())
            ->setType('container')
            ->setAction('create');

        $listener->handle($event);
    }

    /** @test */
    public function it ignore a container without attributes()
    {
        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $domainNameExtractor = $this->prophesize(DomainNameExtractor::class);
        $domainNameExtractor->provideDomainNames(Argument::any())->shouldNotBeCalled();

        $listener = new UpdateHostsFile(
            $hostsFileManager->reveal(),
            $domainNameExtractor->reveal()
        );

        $event = (new EventsGetResponse200())
            ->setType('container')
            ->setAction('create')
            ->setActor(new EventsGetResponse200Actor());

        $listener->handle($event);
    }

    /** @test */
    public function it handle container create events by adding domain names in the hosts file()
    {
        $actor = new EventsGetResponse200Actor();
        $actor->setAttributes($attributes = new \ArrayObject());

        $event = new EventsGetResponse200();
        $event->setAction('create');
        $event->setActor($actor);

        $domainNameExtractor = $this->prophesize(DomainNameExtractor::class);
        $domainNameExtractor->provideDomainNames($attributes)->willReturn(true);
        $domainNameExtractor->getDomainNames($attributes)->willReturn(['foo.fr', 'bar.fr']);

        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $hostsFileManager->addDomainName('foo.fr')->shouldBeCalledTimes(1);
        $hostsFileManager->addDomainName('bar.fr')->shouldBeCalledTimes(1);
        $hostsFileManager->updateHostsFile()->shouldBeCalledTimes(1);

        $listener = new UpdateHostsFile(
            $hostsFileManager->reveal(),
            $domainNameExtractor->reveal()
        );

        $listener->handle($event);
    }

    /** @test */
    public function it handle container destroy events by removing hostnames in the hosts file()
    {
        $actor = new EventsGetResponse200Actor();
        $actor->setAttributes($attributes = new \ArrayObject());

        $event = new EventsGetResponse200();
        $event->setAction('destroy');
        $event->setActor($actor);

        $domainNameExtractor = $this->prophesize(DomainNameExtractor::class);
        $domainNameExtractor->provideDomainNames($attributes)->willReturn(true);
        $domainNameExtractor->getDomainNames($attributes)->willReturn(['foo.fr', 'bar.fr']);

        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $hostsFileManager->removeDomainName('foo.fr')->shouldBeCalledTimes(1);
        $hostsFileManager->removeDomainName('bar.fr')->shouldBeCalledTimes(1);
        $hostsFileManager->updateHostsFile()->shouldBeCalledTimes(1);

        $listener = new UpdateHostsFile(
            $hostsFileManager->reveal(),
            $domainNameExtractor->reveal()
        );

        $listener->handle($event);
    }
}