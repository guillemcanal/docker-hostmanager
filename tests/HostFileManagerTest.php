<?php

namespace ElevenLabs\DockerHostManager;

use ElevenLabs\DockerHostManager\File\File;
use ElevenLabs\DockerHostManager\File\LocalFile;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;

class HostFileManagerTest extends TestCase
{
    private $rootDirectory;

    public function setUp()
    {
        $this->rootDirectory = vfsStream::setup('etc');
    }

    private function getHostsFile(): File
    {
        return LocalFile::get($this->rootDirectory->url() . '/hosts');
    }

    /** @test */
    public function it throw an exception when the hosts file cannot be found()
    {
        $this->expectException(\UnexpectedValueException::class);

        new HostsFileManager($this->getHostsFile());
    }

    /** @test */
    public function it should create a docker stack fenced block if not present in a hosts file()
    {
        $actualContent = '127.0.0.1 localhost';
        $expectedContent = implode("\n",
            [
                '127.0.0.1 localhost',
                '#<docker-stack>',
                '#</docker-stack>',
            ]
        );

        $this->addHostsFile($actualContent);

        new HostsFileManager($this->getHostsFile());

        assertThat($this->getHostsFile()->read(), equalTo($expectedContent));
    }

    /**
     * @test
     * @dataProvider getHostsFilesWithAnEmptyFencedBlocks
     */
    public function it does not provide any domain names when the fenced block(string $hostsFileContent)
    {
        $this->addHostsFile($hostsFileContent);

        $hostsFileManager = new HostsFileManager($this->getHostsFile());

        assertThat($hostsFileManager->getDomainNames(), isEmpty());
    }

    public function getHostsFilesWithAnEmptyFencedBlocks(): array
    {
        return [
            'is empty' => [
                implode("\n",
                    [
                        '127.0.0.1 localhost',
                        '#<docker-stack>',
                        '#</docker-stack>',
                    ]
                )
            ],
            'contains empty lines' => [
                implode("\n",
                    [
                        '127.0.0.1 localhost',
                        '#<docker-stack>',
                        '',
                        '',
                        '#</docker-stack>',
                    ]
                )
            ]
        ];
    }

    /** @test */
    public function it can extract hostnames from a docker stack fenced block()
    {
        $actualContent = implode("\n",
            [
                '127.0.0.1 localhost',
                '#<docker-stack>',
                '127.0.0.1 dev.foo.fr #foo',
                '127.0.0.1 dev.bar.fr #bar',
                '#</docker-stack>',
            ]
        );

        $this->addHostsFile($actualContent);

        $hostFileManager = new HostsFileManager($this->getHostsFile());

        assertThat(
            $hostFileManager->getDomainNames(),
            equalTo(
                [
                    (new DomainName('dev.foo.fr', 'foo'))->withIpv4('127.0.0.1'),
                    (new DomainName('dev.bar.fr', 'bar'))->withIpv4('127.0.0.1')
                ]
            )
        );

    }

    /** @test */
    public function it can add a hostname in an existing docker stack fenced block()
    {
        $actualContent = implode("\n",
            [
                '127.0.0.1 localhost',
                '#<docker-stack>',
                '127.0.0.1 dev.foo.fr #foo',
                '127.0.0.1 dev.bar.fr #bar',
                '#</docker-stack>',
            ]
        );
        $expectedContent = implode("\n",
            [
                '127.0.0.1 localhost',
                '#<docker-stack>',
                '127.0.0.1 dev.foo.fr #foo',
                '127.0.0.1 dev.bar.fr #bar',
                '127.0.0.1 dev.baz.fr #baz',
                '#</docker-stack>',
            ]
        );

        $this->addHostsFile($actualContent);
        $hostsFile = $this->getHostsFile();
        $expectedDomainName = new DomainName('dev.baz.fr', 'baz');

        $hostsFileManager = new HostsFileManager($hostsFile);
        $hostsFileManager->addDomainName($expectedDomainName);
        $hostsFileManager->updateHostsFile();

        assertTrue($hostsFileManager->hasDomainName($expectedDomainName));
        assertThat($hostsFile->read(), equalTo($expectedContent));
    }

    /** @test */
    public function it throw an exception when trying to add a domain name that already exist()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Domain name dev.foo.fr is already associated with foo');

        $actualContent = implode("\n",
            [
                '127.0.0.1 localhost',
                '#<docker-stack>',
                '127.0.0.1 dev.foo.fr #foo',
                '#</docker-stack>',
            ]
        );

        $this->addHostsFile($actualContent);

        $hostsFileManager = new HostsFileManager($this->getHostsFile());
        $hostsFileManager->addDomainName(new DomainName('dev.foo.fr', 'something'));
    }

    /** @test */
    public function it can remove a hostname in an existing docker stack fenced block()
    {
        $actualContent = implode("\n",
            [
                '127.0.0.1 localhost',
                '#<docker-stack>',
                '127.0.0.1 dev.foo.fr #foo',
                '127.0.0.1 dev.bar.fr #bar',
                '#</docker-stack>',
            ]
        );
        $expectedContent = implode("\n",
            [
                '127.0.0.1 localhost',
                '#<docker-stack>',
                '127.0.0.1 dev.foo.fr #foo',
                '#</docker-stack>',
            ]
        );

        $this->addHostsFile($actualContent);
        $hostsFile = $this->getHostsFile();
        $domainNameToRemove = new DomainName('dev.bar.fr', 'bar');

        $hostsFileManager = new HostsFileManager($hostsFile);
        $hostsFileManager->removeDomainName($domainNameToRemove);
        $hostsFileManager->updateHostsFile();

        assertFalse($hostsFileManager->hasDomainName($domainNameToRemove));
        assertThat($hostsFile->read(), equalTo($expectedContent));
    }

    /** @test */
    public function it throw an exception when it cant extract a domaine name()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to parse the container domain string: invalid');

        $actualContent = implode("\n",
            [
                '127.0.0.1 localhost',
                '#<docker-stack>',
                'invalid',
                '#</docker-stack>',
            ]
        );

        $this->addHostsFile($actualContent);

        new HostsFileManager($this->getHostsFile());
    }

    private function addHostsFile($content = '', $permission = 0644): vfsStreamFile
    {
        $file = vfsStream::newFile('hosts', $permission)->withContent($content);
        $this->rootDirectory->addChild($file);

        return $file;
    }
}
