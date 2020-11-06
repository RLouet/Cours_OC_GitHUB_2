<?php


namespace Core;

use \Twig;


class HTTPResponse
{

    public function addHeader(string $header)
    {
        header($header);
    }

    public static function redirect(string $location)
    {
        header('location: http://' . $_SERVER['HTTP_HOST'] . $location, true, 303);
        exit;
    }

    public function redirect404()
    {

    }

    /**
     * @param $template
     * @param array $args
     * @throws Twig\Error\LoaderError
     * @throws Twig\Error\RuntimeError
     * @throws Twig\Error\SyntaxError
     */
    public static function renderTemplate (string $template, array $args = [])
    {
        static $twig = null;

        if ($twig === null) {
            $loader = new Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/Templates');
            $twig = new Twig\Environment($loader, [
                //'cache' => '../cache'
            ]);
        }
        $args['path'] = 'http://' . $_SERVER['HTTP_HOST'];
        echo $twig->render($template, $args);
    }

        public function setCookie($name, $value = '', $expire = 0, $path = null, $domain = null, $secure = false, $httpOnly = true)
    {
        $this->setCookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }
}