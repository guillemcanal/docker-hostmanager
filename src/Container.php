<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager;

class Container
{
    private $name;
    private $labels;

    public function __construct($name, ?\ArrayObject $labels)
    {
        $this->name = $name;
        $this->labels = $labels ?: new \ArrayObject();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabels(): \ArrayObject
    {
        return $this->labels;
    }
}