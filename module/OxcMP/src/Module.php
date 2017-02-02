<?php

namespace OxcMP;

class Module
{
    const VERSION = '3.0.1';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
