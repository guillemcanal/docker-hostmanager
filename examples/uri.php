<?php

use ElevenLabs\DockerHostManager\File\FileFactory;
use ElevenLabs\DockerHostManager\File\LocalFile;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = \org\bovigo\vfs\vfsStream::setup('root', 755);
$factory = new FileFactory(LocalFile::class);

$file = $factory->getFile('certs/lol.crt');

if ($file->exists()) {
    print 'exists' .PHP_EOL;
}

$file->put('hello');

print $file->read();