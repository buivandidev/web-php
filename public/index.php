<?php
// public/index.php

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEW_PATH', ROOT_PATH . '/views');

// Custom PSR-4 Autoloader mapping App\ to app/
spl_autoload_register(function (string $class) {
    if (str_starts_with($class, 'App\\')) {
        $relativeClass = substr($class, 4);
        $path = APP_PATH . '/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    }
});

// Initialize session
App\Core\Session::start();

// Load DI container and router
$container = require_once CONFIG_PATH . '/app.php';
$router    = require_once CONFIG_PATH . '/routes.php';

// Dispatch route
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
