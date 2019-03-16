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

    public function setUp(): void
    {
        $this->rootDirectory = vfsStream::setup('etc');
    }

    private function getHostsFile(): File
    {
        return LocalFile::get($this->rootDirectory->url() . '/hosts');
    }

    /** @test */
    public function it_throw_an_exception_when_the_hosts_file_cannot_be_found()
    {
        $this->expectException(\UnexpectedValueException::class);

        new HostsFileManager($this->getHostsFile());
    }

    /** @test */
    public function it_should_create_a_docker_stack_fenced_block_if_not_present_in_a_hosts_file()
    {
        $actualContent = '127.0.0.1 localhost';
        $expectedContent = implode("\n",
            [
                '127.0.0.1 localhost',
                '#<docker-hostmanager>',
                '#</docker-hostmanager>',
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
    public function it_does_not_provide_any_domain_names_when_the_fenced_block(string $hostsFileContent)
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
                        '#<docker-hostmanager>',
                        '#</docker-hostmanager>',
                    ]
                )
            ],
            'contains empty lines' => [
                implode("\n",
                    [
                        '127.0.0.1 localhost',
                        '#<docker-hostmanager>',
                        '',
                        '',
                        '#</docker-hostmanager>',
                    ]
                )
            ]
        ];
    }

    /** @test */
    public function it_can_extract_hostnames_from_a_docker_stack_fenced_block()
    {
        $actualContent = implode("\n",
            [
                '127.0.0.1 localhost',
                '#<docker-hostmanager>',
                '127.0.0.1 dev.foo.fr #foo',
                '127.0.0.1 dev.bar.fr #bar',
                '#</docker-hostmanager>',
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
    public function it_can_add_a_hostname_in_an_existing_docker_stack_fenced_block()
    {
        $actualContent = implode("\n",
            [
                '127.0.0.1 localhost',
                '#<docker-hostmanager>',
                '127.0.0.1 dev.foo.fr #foo',
                '127.0.0.1 dev.bar.fr #bar',
                '#</docker-hostmanager>',
            ]
        );
        $expectedContent = implode("\n",
            [
                '127.0.0.1 localhost',
                '#<docker-hostmanager>',
                '127.0.0.1 dev.foo.fr #foo',
                '127.0.0.1 dev.bar.fr #bar',
                '127.0.0.1 dev.baz.fr #baz',
                '#</docker-hostmanager>',
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
    public function it_throw_an_exception_when_trying_to_add_a_domain_name_that_already_exist()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Domain name dev.foo.fr is already associated with foo');

        $actualContent = implode("\n",
            [
                '127.0.0.1 localhost',
                '#<docker-hostmanager>',
                '127.0.0.1 dev.foo.fr #foo',
                '#</docker-hostmanager>',
            ]
        );

        $this->addHostsFile($actualContent);

        $hostsFileManager = new HostsFileManager($this->getHostsFile());
        $hostsFileManager->addDomainName(new DomainName('dev.foo.fr', 'something'));
    }

    /** @test */
    public function it_can_remove_a_hostname_in_an_existing_docker_stack_fenced_block()
    {
        $actualContent = implode("\n",
            [
                '127.0.0.1 localhost',
                '#<docker-hostmanager>',
                '127.0.0.1 dev.foo.fr #foo',
                '127.0.0.1 dev.bar.fr #bar',
                '#</docker-hostmanager>',
            ]
        );
        $expectedContent = implode("\n",
            [
                '127.0.0.1 localhost',
                '#<docker-hostmanager>',
                '127.0.0.1 dev.foo.fr #foo',
                '#</docker-hostmanager>',
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
    public function it_throw_an_exception_when_it_cant_extract_a_domaine_name()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to parse the container domain string: invalid');

        $actualContent = implode("\n",
            [
                '127.0.0.1 localhost',
                '#<docker-hostmanager>',
                'invalid',
                '#</docker-hostmanager>',
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
