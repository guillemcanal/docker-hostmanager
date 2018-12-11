<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\ContainersIdExecPostBody;
use Docker\API\Model\ExecIdStartPostBody;
use Docker\API\Model\IdResponse;
use Docker\Stream\DockerRawStream;
use ElevenLabs\DockerHostManager\Event\ContainerCreated;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;
use Docker\Docker;

class AddRootCertificateInContainer implements EventListener
{
    /** @var Docker  */
    private $docker;
    /** @var string */
    private $rootCa;

    public function __construct(Docker $docker)
    {
        $this->docker = $docker;
    }

    public function subscription(): EventSubscription
    {
        return new EventSubscription(
            ContainerCreated::class,
            function (ContainerCreated $event) {
                $this->addRootCertificate($event);
            }
        );
    }

    public function addRootCertificate(ContainerCreated $event): void
    {
        /** @TODO load RootCA contents somewhere else */
        if (!$this->rootCa) {
            $rootCaFile = '/data/root-ca.crt';
            if (!file_exists($rootCaFile)) {
                return;
            }
            $this->rootCa = file_get_contents($rootCaFile);
        }

        $caCertificatesDir = '/usr/local/share/ca-certificates';

        /** @var IdResponse $execIdResponse */
        $execIdResponse = $this->docker->containerExec(
            $event->getContainerName(),
            (new ContainersIdExecPostBody())
                ->setCmd(
                    [
                        'sh', '-c',
                        implode(PHP_EOL, [
                            'mkdir -p ' . $caCertificatesDir,
                            'cat << EOF > ' . $caCertificatesDir . '/root-ca.crt',
                            $this->rootCa,
                            'EOF',
                            'update-ca-certificates --fresh || true'
                        ])
                    ]
                )
        );

        /** @var DockerRawStream $stream */
        $this->docker->execStart(
            $execIdResponse->getId(),
            (new ExecIdStartPostBody())
                ->setDetach(false)
                ->setTty(false)
        );
    }
}
