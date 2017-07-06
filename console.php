#!/usr/bin/env php
<?php
// application.php

require 'autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new \mitch\schemacompare\commands\InspectDbCommand());
$application->add(new \mitch\schemacompare\commands\SyncCommand());

$application->run();