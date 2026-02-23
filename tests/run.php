<?php

require_once __DIR__ . '/../tests/TestRunner.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Load app classes if not already

// Mock environment
$_ENV['APP_ENV'] = 'testing';
date_default_timezone_set('UTC');

$runner = new \Tests\TestRunner();
$runner->runDir(__DIR__);
