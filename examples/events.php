<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use ElevenLabs\DockerHostManager\DockerEvents;
use ElevenLabs\DockerHostManager\File\InMemoryFile;
use ElevenLabs\DockerHostManager\HostsFileManager;
use ElevenLabs\DockerHostManager\HostsProvider\TraefikHostsProvider;
use ElevenLabs\DockerHostManager\Listener\HostManagerListener;

$fakeHostsFile = new class extends InMemoryFile
{
    public function putContents(string $contents): void
    {
        parent::putContents($contents);
        print $contents . PHP_EOL;
    }
};

$events = new DockerEvents();
$events->addListener(
    new HostManagerListener(
        new HostsFileManager($fakeHostsFile),
        new TraefikHostsProvider()
    )
);

$events->run();