<?php

namespace ElevenLabs\DockerHostManager;

use ElevenLabs\DockerHostManager\File\InMemoryFile;
use PHPUnit\Framework\TestCase;

class HostFileManagerTest extends TestCase
{
    /** @test */
    public function it throw an exception when the hosts file cannot be found()
    {
        $this->expectException(\UnexpectedValueException::class);
        new HostsFileManager(new InMemoryFile('', $shouldExists = false));
    }

    /** @test */
    public function it should create a docker stack fenced block if not present in a hosts file()
    {
        $contents = '127.0.0.1 localhost localdomain';
        $hostsFile = new InMemoryFile($contents);

        new HostsFileManager($hostsFile);

        assertThat($hostsFile->getContents(), stringContains("#<docker-stack>\n#</docker-stack>"));
    }

    /** @test */
    public function it can extract hostnames from a docker stack fenced block()
    {
        $contents = implode("\n",
            [
                '127.0.0.1 localhost localdomain',
                '#<docker-stack>',
                '127.0.0.1 dev.foo.fr',
                '127.0.0.1 dev.bar.fr',
                '#</docker-stack>',
            ]
        );

        $hostFileManager = new HostsFileManager(new InMemoryFile($contents));

        assertTrue($hostFileManager->hasHostname('dev.foo.fr'));
        assertTrue($hostFileManager->hasHostname('dev.bar.fr'));
    }

    /** @test */
    public function it can add a hostname in an existing docker stack fenced block()
    {
        $actualContent = implode("\n",
            [
                '127.0.0.1 localhost localdomain',
                '#<docker-stack>',
                '127.0.0.1 dev.foo.fr',
                '127.0.0.1 dev.bar.fr',
                '#</docker-stack>',
            ]
        );
        $expectedContent = implode("\n",
            [
                '127.0.0.1 localhost localdomain',
                '#<docker-stack>',
                '127.0.0.1 dev.foo.fr',
                '127.0.0.1 dev.bar.fr',
                '127.0.0.1 dev.baz.fr',
                '#</docker-stack>',
            ]
        );

        $hostsFile = new InMemoryFile($actualContent);

        $hostsFileManager = new HostsFileManager($hostsFile);
        $hostsFileManager->addHostname('dev.baz.fr');
        $hostsFileManager->updateHostsFile();

        assertTrue($hostsFileManager->hasHostname('dev.baz.fr'));
        assertThat($hostsFile->getContents(), equalTo($expectedContent));
    }

    /** @test */
    public function it can remove a hostname in an existing docker stack fenced block()
    {
        $actualContent = implode("\n",
            [
                '127.0.0.1 localhost localdomain',
                '#<docker-stack>',
                '127.0.0.1 dev.foo.fr',
                '127.0.0.1 dev.bar.fr',
                '#</docker-stack>',
            ]
        );
        $expectedContent = implode("\n",
            [
                '127.0.0.1 localhost localdomain',
                '#<docker-stack>',
                '127.0.0.1 dev.foo.fr',
                '#</docker-stack>',
            ]
        );

        $hostsFile = new InMemoryFile($actualContent);

        $hostsFileManager = new HostsFileManager($hostsFile);
        $hostsFileManager->removeHostname('dev.bar.fr');
        $hostsFileManager->updateHostsFile();

        assertFalse($hostsFileManager->hasHostname('dev.baz.fr'));
        assertThat($hostsFile->getContents(), equalTo($expectedContent));
    }

    /** @test */
    public function it throw an exception when it cant extract exactly one ip address and one hostname()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected exactly one IP address and one hostname, got oups');

        $content = implode("\n",
            [
                '127.0.0.1 localhost localdomain',
                '#<docker-stack>',
                'oups',
                '#</docker-stack>',
            ]
        );

        new HostsFileManager(new InMemoryFile($content));
    }
}