<?php

use DavidMorenoCortina\JWT\Config\DBInitialization;
use DavidMorenoCortina\JWT\Exception\DBException;

require __DIR__ . '/vendor/autoload.php';

$settings = require __DIR__ . '/phpunit-settings.php';

$dsn = 'mysql:host=' . $settings['db']['host'] . ';port=' . $settings['db']['port'] . ';dbname=' . $settings['db']['dbName'];
$conn = new PDO($dsn, $settings['db']['user'], $settings['db']['password'], [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);

$dbInitialization = new DBInitialization();

$dbInitialization->createRSATable($conn);

try {
    $dbInitialization->createRsaKey($conn, 'tests');
} catch (DBException $e) {
    echo "Could not initialize the database\n";
    die();
}

$dbInitialization->createUserTable($conn);

try {
    $dbInitialization->createUser($conn, 'demo', 'test', true);
} catch (DBException $e) {
    echo "Could not initialize the database\n";
    die();
}