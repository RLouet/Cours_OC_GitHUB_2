<?php

use Core\Router;
use Core\HTTPRequest;

/**
 * Composer Autoloader
 */
require_once __DIR__ . '/../vendor/autoload.php';

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