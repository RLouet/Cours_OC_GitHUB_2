<?php


namespace Core;

use Throwable;

class Error
{

    /**
     * Exception handler.
     *
     * @param Throwable $exception
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     *
     * @return void
     */
    public static function exceptionHandler(Throwable $exception)
    {
        $config = Config::getInstance();
        if ($config->get('show_errors') === "true") {
            http_response_code($exception->getCode());
            echo "<h1>Fatal error</h1>";
            echo "<p>Uncaught exception : '" . get_class($exception) . "'</p>";
            echo "<p>Message : '" . $exception->getMessage() . "'</p>";
            echo "<p>Stack trace : <pre>" . $exception->getTraceAsString() . "</pre></p>";
            echo "<p>Thrown in '" . $exception->getFile() . "' on line " . $exception->getLine() . "</p>";
        } else {
            // Code is 404 (not found) or 500 (general error)
            $code = $exception->getCode();
            if ($code != 404) {
                $code = 500;
            }
            http_response_code($code);

            $log = dirname(__DIR__) . '/logs/' . date('Y-m-d') . '.txt';
            ini_set('error_log', $log);

            $message = "Uncaught exception : '" . get_class($exception) . "'";
            $message .= " Message : '" . $exception->getMessage() . "'";
            $message .= "\nStack trace : " . $exception->getTraceAsString();
            $message .= "\nThrown in '" . $exception->getFile() . "' on line " . $exception->getLine();

            error_log($message);

            HTTPResponse::renderTemplate("Errors/$code.html.twig");
        }
    }
}