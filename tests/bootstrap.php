<?php

chdir(dirname(__DIR__));
ini_set('display_errors', true);
define("APPLICATION_ENV", "local");
define('DS', DIRECTORY_SEPARATOR);
define('BASE_DIR', '/home/dhirendra/workspace/hb/munch');

include_once __DIR__ . '/../init_autoloader.php';
$application = Zend\Mvc\Application::init(require 'config/application.config.php');
\MCommons\StaticOptions::setServiceLocator($application->getServiceManager());