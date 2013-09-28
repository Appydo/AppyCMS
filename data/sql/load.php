<?php

/**
 * Script for creating and loading database
 */
chdir(dirname(__DIR__));
$global = include 'config/autoload/global.php';
$dsn = $global['db']['dsn'];


if (substr($dsn, 0, 7) == 'sqlite:') {
    $type = 'sqlite';
} else {
    $type = 'mysql';
}

// ======================================= //
//                                         //
// ======================================= //
// Check to see if we have a database file already
$dbFile = substr($dsn, 7);
if (file_exists($dbFile)) {
    unlink($dbFile);
}

// this block executes the actual statements that were loaded from
// the schema file.
if ($type == 'sqlite') {
    $schemaSql = file_get_contents(dirname(__FILE__) . '/schema.sqlite.sql');
    try {
        $database = new PDO('sqlite:' . $dbFile);
    } catch (Exception $e) {
        die('sqlite create error');
    }
    if (!$database->exec($schemaSql)) {
        // die('exec error');
    }
} else {
    $schemaSql = file_get_contents(dirname(__FILE__) . '/schema.mysql.sql');
    $dbAdapter->getConnection()->exec($schemaSql);
}

echo PHP_EOL;
echo 'Database Created';
echo PHP_EOL;

if (true or isset($withData)) {
    $dataSql = file_get_contents(dirname(__FILE__) . '/data.sqlite.sql');
    $database->exec($dataSql);
    echo 'Data Loaded.';
    echo PHP_EOL;
}

// generally speaking, this script will be run from the command line
return true;
