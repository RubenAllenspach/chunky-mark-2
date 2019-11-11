<?php

$dc = [];

$dc['db'] = new \Lib\SQLiteManager\SQLiteManager(__DIR__ . '/../../application.sqlite3');

// twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../view');
$dc['twig'] = new \Twig\Environment(
    $loader/*,
    ['cache' => __DIR__ . '/../../var/cache',]*/
);
