<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\DomainName;
use ElevenLabs\DockerHostManager\Event\DomainNamesAdded;
use ElevenLabs\DockerHostManager\Event\ErrorReceived;
use ElevenLabs\DockerHostManager\Event\EventProcessed;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducer;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducerTrait;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;
use ElevenLabs\DockerHostManager\HostsFileManager;

class AddDomainNames implements EventListener, EventProducer
{
    use EventProducerTrait;

    private $hostsFileManager;

    public function __construct(HostsFileManager $hostsFileManager)
    {
        $this->hostsFileManager = $hostsFileManager;
    }

    public function handle(DomainNamesAdded $event): void
    {
        foreach ($event->getDomainNames() as $domainNameString) {
            $domainName = new DomainName($domainNameString, $event->getContainerName());
            try {
                $this->hostsFileManager->addDomainName($domainName);
            } catch (\Exception $e) {
                $this->produceEvent(
                    new ErrorReceived(
                        \sprintf(
                            'Unable to add domain name %s for container %s',
                            $domainName->getName(),
                            $domainName->getContainerName()
                        ),
                        $e
                    )
                );
            }
        }

        $this->hostsFileManager->updateHostsFile();
        $this->produceEvent(new EventProcessed('Added domain names in the host file'));
    }

    public function subscription(): EventSubscription
    {
        return new EventSubscription(
            DomainNamesAdded::class,
            function (DomainNamesAdded $event): void {
                $this->handle($event);
            }
        );
    }
}
