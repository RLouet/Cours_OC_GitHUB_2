<?php

/**
 * Composer Autoloader
 */
require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Error and exception handling
 */
error_reporting(E_ALL);
set_exception_handler('\Core\Error::exceptionHandler');

/**
 * Session
 */
session_start();

/**
 * Routing
 */
$router = new \Core\Router();
$request = new \Core\HTTPRequest();

$router->dispatch($request);

//$kernel = new \Core\Kernel();

//$kernel->run();


//$router->dispatch($_SERVER['QUERY_STRING']);