#!/usr/bin/env php
<?php
// application.php

require __DIR__ . '/vendor/autoload.php';
spl_autoload_register(function ($name) {
    $filename = str_replace('\\', '/', $name) . '.php';
    if (file_exists($filename)) {
        require $filename;
    }
});

use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new \mitch\schemacompare\commands\InspectDbCommand());
$application->add(new \mitch\schemacompare\commands\SyncCommand());

$application->run();