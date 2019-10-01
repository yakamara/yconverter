<?php

namespace YConcerter\Package;

use YConverter\Config;

abstract class Package
{
    protected $config;

    abstract function getName(): string;

    abstract function getTables(): array;

    abstract function updateTableStructure();

    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    protected function getConfig(): Config
    {
        return $this->config;
    }

}
