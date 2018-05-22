<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use ElevenLabs\DockerHostManager\DockerEvents;
use ElevenLabs\DockerHostManager\File\LocalFile;
use ElevenLabs\DockerHostManager\HostsFileManager;
use ElevenLabs\DockerHostManager\HostsExtractor\TraefikFrontendRule;
use ElevenLabs\DockerHostManager\Listener\HostManagerListener;
use ElevenLabs\DockerHostManager\VerifyManagedHosts;

$docker           = \Docker\Docker::create();
$hostsFileManager = new HostsFileManager(new LocalFile('/etc/hosts'));
$hostsExtractors  = [new TraefikFrontendRule()];

// Verify the state of the hosts file
$verifier = new VerifyManagedHosts($hostsFileManager, $hostsExtractors, $docker);
$verifier->verify();

// Listen to Docker events for 30 seconds
(new DockerEvents($docker))
    ->addListener(new HostManagerListener($hostsFileManager, $hostsExtractors))
    ->listenSince(5)
    ->listenUntil(30)
    ->listen();
