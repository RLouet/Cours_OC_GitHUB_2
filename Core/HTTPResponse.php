<?php


namespace Core;

use Blog\Models\BlogManagerPDO;
use \Twig;
use Twig\TwigFunction;


class HTTPResponse
{

    public function addHeader(string $header)
    {
        header($header);
    }

    public function redirect(string $location)
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
    public function renderTemplate (string $template, array $args = [], bool $messages = true)
    {
        echo $this->getTemplate($template, $args, $messages);
    }

    /**
     * @param string $template
     * @param array $args
     * @param bool $messages
     * @return string
     * @throws Twig\Error\LoaderError
     * @throws Twig\Error\RuntimeError
     * @throws Twig\Error\SyntaxError
     */
    public function getTemplate (string $template, array $args = [], bool $messages = true)
    {
        static $twig = null;

        if ($twig === null) {
            $loader = new Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/Templates');
            $twig = new Twig\Environment($loader, [
                //'cache' => '../cache'
            ]);
            $twig->addGlobal('path', 'http://' . $_SERVER['HTTP_HOST']);
            $twig->addGlobal('current_user', Auth::getUser());
            if ($messages) {
                $twig->addGlobal('flash_messages', Flash::getMessages());
            }
            $twig->addGlobal('blog', $this->getBlog());
        }
        return $twig->render($template, $args);
    }

    /**
     * @param string $template
     * @param array $args
     * @return string
     * @throws Twig\Error\LoaderError
     * @throws Twig\Error\RuntimeError
     * @throws Twig\Error\SyntaxError
     */
    public function getMailTemplate (string $template, array $args = [])
    {
        static $twig2 = null;

        if ($twig2 === null) {
            $loader = new Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/Templates');
            $twig2 = new Twig\Environment($loader, [
                //'cache' => '../cache'
            ]);
            $twig2->addGlobal('path', 'http://' . $_SERVER['HTTP_HOST']);
            $twig2->addGlobal('current_user', Auth::getUser());
            $twig2->addGlobal('blog', $this->getBlog());
        }
        return $twig2->render($template, $args);
    }


    public function setCookie($name, $value = '', $expire = 0, $path = null, $domain = null, $secure = false, $httpOnly = true)
    {
        setCookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    private function getBlog()
    {
        $config = Config::getInstance();
        $blogId = $config->get('blog_id') ? $config->get('blog_id') : 1;
        $blogManager = new BlogManagerPDO(PDOFactory::getPDOConnexion());
        return $blogManager->getData($blogId);
    }
}