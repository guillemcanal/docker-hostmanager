<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\DomainName;
use ElevenLabs\DockerHostManager\Event\DomainNamesRemoved;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;
use ElevenLabs\DockerHostManager\HostsFileManager;

class RemoveDomainNames implements EventListener
{
    private $hostsFileManager;

    public function __construct(HostsFileManager $hostsFileManager)
    {
        $this->hostsFileManager = $hostsFileManager;
    }

    public function handle(DomainNamesRemoved $event): void
    {
        foreach ($event->getDomainNames() as $domainNameString) {
            $this->hostsFileManager->removeDomainName(new DomainName($domainNameString, $event->getContainerName()));
        }
        $this->hostsFileManager->updateHostsFile();
    }

    public function subscription(): EventSubscription
    {
        return new EventSubscription(
            DomainNamesRemoved::class,
            function (DomainNamesRemoved $event): void {
                $this->handle($event);
            }
        );
    }
}
