<?php

/**
 * Composer Autoloader
 */

use Core\Router;
use Core\HTTPRequest;

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
 * Set timezone
 */
date_default_timezone_set("Europe/Paris");

/**
 * Routing
 */
$router = new Router();
$request = HTTPRequest::getInstance();

$router->dispatch($request);

//$kernel = new \Core\Kernel();

//$kernel->run();


//$router->dispatch($_SERVER['QUERY_STRING']);