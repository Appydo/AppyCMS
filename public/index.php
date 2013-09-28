<?php
setlocale(LC_TIME, "fr_FR.utf8");
header('Content-type: text/html; charset=utf-8"');
define('ROOT_PATH', dirname(__DIR__).'/');
chdir(dirname(__DIR__));

// Setup autoloading
include 'config/init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(include 'config/application.config.php')->run();
