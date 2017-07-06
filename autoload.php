<?php

require __DIR__ . '/vendor/autoload.php';
spl_autoload_register(function ($name) {
    $filename = str_replace('\\', '/', $name) . '.php';
    if (file_exists($filename)) {
        require $filename;
    }
});