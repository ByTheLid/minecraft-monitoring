<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\App;
use App\Core\ExceptionHandler;

ExceptionHandler::register();

$app = new App();
$app->run();
