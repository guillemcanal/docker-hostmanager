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
        new HostsFileManager(new InMemoryFile('', $souldExists = false));
    }

    /** @test */
    public function it should create a docker stack fenced block if not present in the hosts file()
    {
        $contents = '127.0.0.1 localhost localdomain';
        $hostsFile = new InMemoryFile($contents);

        new HostsFileManager($hostsFile);

        assertThat($hostsFile->getContents(), stringContains("#<docker-stack>\n#</docker-stack>"));
    }

    /** @test */
    public function it extract hosts from the docker stack fenced block()
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

        assertTrue($hostFileManager->hasDomain('dev.foo.fr'));
        assertTrue($hostFileManager->hasDomain('dev.bar.fr'));
    }

    /** @test */
    public function it can add an host in the docker stack fenced block()
    {
        $actualContent = implode("\n",
            [
                '127.0.0.1 localhost',
            ]
        );
        $expectedContent = implode("\n",
            [
                '127.0.0.1 localhost',
                '#<docker-stack>',
                '127.0.0.1 dev.bat.fr',
                '#</docker-stack>',
                ''
            ]
        );

        $hostsFile = new InMemoryFile($actualContent);

        $hostsFileManager = new HostsFileManager($hostsFile);
        $hostsFileManager->addDomain('dev.bat.fr');
        $hostsFileManager->updateHostsFile();

        assertTrue($hostsFileManager->hasDomain('dev.bat.fr'));
        assertThat($hostsFile->getContents(), equalTo($expectedContent));
    }

    /** @test */
    public function it can append an host in an existing docker stack fenced block()
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
        $hostsFileManager->addDomain('dev.baz.fr');
        $hostsFileManager->updateHostsFile();

        assertTrue($hostsFileManager->hasDomain('dev.baz.fr'));
        assertThat($hostsFile->getContents(), equalTo($expectedContent));
    }

    /** @test */
    public function it can remove an host in an existing docker stack fenced block()
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
        $hostsFileManager->removeDomain('dev.bar.fr');
        $hostsFileManager->updateHostsFile();

        assertFalse($hostsFileManager->hasDomain('dev.baz.fr'));
        assertThat($hostsFile->getContents(), equalTo($expectedContent));
    }

    /** @test */
    public function it throw an exception when it cant extract exactly one ip address and an host()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected exactly one IP address and one domain, got oups');

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