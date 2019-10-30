<?php

$dc = [];

// db
$sqlite = new PDO('sqlite:' . __DIR__ . '/../../application.sqlite3');
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sqlite->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$dc['db'] = $sqlite;

// twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../view');
$dc['twig'] = new \Twig\Environment(
    $loader/*,
    ['cache' => __DIR__ . '/../../var/cache',]*/
);
