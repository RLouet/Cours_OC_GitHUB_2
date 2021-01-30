<?php


namespace Core;


class HTTPRequest
{
    private static ?HTTPRequest $instance = null;
    private array $cookies;
    private array $get;
    private array $post;
    private array $files;
    private array $server;
    private array $session;

    private function __construct()
    {
        $this->cookies = $_COOKIE;
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->server = $_SERVER;
        $this->session = $_SESSION;
    }

    public static function getInstance()
    {
        if(is_null(self::$instance))
        {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function sessionData(string $key)
    {
        return isset($this->session[$key]) ? $this->session['key'] : null;
    }

    public function cookieData(string $key)
    {
        return isset($this->cookies[$key]) ? $this->cookies['key'] : null;
    }

    public function cookieExists(string $key): bool
    {
        return isset($this->cookies[$key]);
    }

    public function getData($key)
    {
        return isset($this->get[$key]) ? $this->get[$key] : null;
    }

    public function getExists($key)
    {
        return isset($this->get[$key]);
    }

    public function method()
    {
        return $this->server['REQUEST_METHOD'];
    }

    public function requestUri()
    {
        return $this->server['REQUEST_URI'];
    }

    public function postData(string $key = null)
    {
        if ($key) {
            return isset($_POST[$key]) ? $_POST[$key] : null;
        }
        return $_POST;
    }

    public function postExists($key)
    {
        return isset($_POST[$key]);
    }

    public function requestQueryString()
    {
        return $_SERVER['QUERY_STRING'];
    }

    public function filesData($key)
    {
        return isset($_FILES[$key]) ? $_FILES[$key] : null;
    }

    public function filesExists($key)
    {
        return isset($_FILES[$key]);
    }

    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }
}