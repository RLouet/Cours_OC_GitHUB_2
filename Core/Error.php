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
        $code = $exception->getCode();
        $config = Config::getInstance();
        if ($config->get('show_errors') === "true") {
            if (!is_int($code)) {
                $code = 500;
            }
            http_response_code($code);
            $response = "<h1>Fatal error</h1>\n" .
                "<h2>Code = " . $code . "</h2>\n" .
                "<p>Uncaught exception : '" . get_class($exception) . "'</p>\n" .
                "<p>Message : '" . $exception->getMessage() . "'</p>\n" .
                "<p>Stack trace : <pre>" . $exception->getTraceAsString() . "</pre></p>\n" .
                "<p>Thrown in '" . $exception->getFile() . "' on line " . $exception->getLine() . "</p>"
            ;
            echo $response;
            return;
        }
        // Code is 404 (not found) or 500 (general error)
        if ($code != 404) {
            $code = 500;
        }
        http_response_code($code);

        $log = __DIR__ . '/../logs/' . date('Y-m-d') . '.txt';
        ini_set('error_log', $log);

        $message = "Uncaught exception : '" . get_class($exception) . "'";
        $message .= " Message : '" . $exception->getMessage() . "'";
        $message .= "\nStack trace : " . $exception->getTraceAsString();
        $message .= "\nThrown in '" . $exception->getFile() . "' on line " . $exception->getLine();

        error_log($message);

        $httpResponse = new HTTPResponse();
        $httpResponse->renderTemplate("Errors/$code.html.twig");
    }

    /**
     * Exception log writer.
     *
     * @param Throwable $exception
     *
     * @return void
     */
    public static function exceptionLogWriter(Throwable $exception)
    {
        $log = __DIR__ . '/../logs/' . date('Y-m-d') . '.txt';
        ini_set('error_log', $log);

        $message = "Uncaught exception : '" . get_class($exception) . "'";
        $message .= " Message : '" . $exception->getMessage() . "'";
        $message .= "\nStack trace : " . $exception->getTraceAsString();
        $message .= "\nThrown in '" . $exception->getFile() . "' on line " . $exception->getLine();

        error_log($message);
    }
}