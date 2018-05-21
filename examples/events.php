<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use ElevenLabs\DockerHostManager\DockerEvents;
use ElevenLabs\DockerHostManager\File\InMemoryFile;
use ElevenLabs\DockerHostManager\File\LocalFile;
use ElevenLabs\DockerHostManager\HostsFileManager;
use ElevenLabs\DockerHostManager\HostsExtractor\TraefikFrontendRule;
use ElevenLabs\DockerHostManager\Listener\HostManagerListener;

$docker = \Docker\Docker::create();

/** @var \Docker\API\Model\ContainersCreatePostResponse201 $container */
$container = $docker->containerCreate(
    (new \Docker\API\Model\ContainersCreatePostBody())
        ->setImage('busybox')
        ->setCmd(['top'])
        ->setLabels(new \ArrayObject(['traefik.frontend.rule' => 'Host: dev.foo.fr']))
);

$hostManager = new HostManagerListener(
    new HostsFileManager(new LocalFile('/etc/hosts')),
    [new TraefikFrontendRule()]
);

$events = (new DockerEvents($docker))
    ->addListener($hostManager)
    ->listenSince(5)
    ->listenUntil(5)
    ->listen();

$docker->containerStop($container->getId());
