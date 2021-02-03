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
        if(self::$instance === null)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function sessionData(string $key)
    {
        return isset($this->session[$key]) ? $this->session[$key] : null;
    }

    public function cookieData(string $key)
    {
        return isset($this->cookies[$key]) ? $this->cookies[$key] : null;
    }

    public function cookieExists(string $key): bool
    {
        return isset($this->cookies[$key]);
    }

    public function getData(string $key)
    {
        return isset($this->get[$key]) ? $this->get[$key] : null;
    }

    public function getExists(string $key)
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

    public function getHost()
    {
        return $this->server['HTTP_HOST'];
    }

    public function postData(string $key = null)
    {
        if ($key) {
            return isset($this->post[$key]) ? $this->post[$key] : null;
        }
        return $this->post;
    }

    public function postExists(string $key)
    {
        return isset($this->post[$key]);
    }

    public function requestQueryString()
    {
        return $this->server['QUERY_STRING'];
    }

    public function filesData(string $key)
    {
        return isset($this->files[$key]) ? $this->files[$key] : null;
    }

    public function filesExists(string $key)
    {
        return isset($this->files[$key]);
    }

    public function isAjax()
    {
        return isset($this->server['HTTP_X_REQUESTED_WITH']) && $this->server['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }
}