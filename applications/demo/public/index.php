<?php

define('DS', DIRECTORY_SEPARATOR);
define('APPLICATION_DIR', dirname(__DIR__) . DS);
define('APPLICATION_PATH', APPLICATION_DIR . 'app'.DS);

$application = new \Yaf\Application(APPLICATION_PATH . 'conf/application.ini');
$application->bootstrap()->run();