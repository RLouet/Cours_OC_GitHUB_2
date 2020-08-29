<?php


namespace Core;


class Kernel
{
    protected $httpRequest;
    protected $config;
    protected $router;

    public function __construct()
    {
        $this->httpRequest = new HTTPRequest();
        $this->config = new Config();
        $this->router = new Router();
    }

    public function httpRequest()
    {
        return $this->httpRequest;
    }

    public function httpResponse()
    {
        return $this->httpResponse;
    }

    public function config()
    {
        return $this->config;
    }

    public function run(){

        $this->router->dispatch($this->httpRequest);
    }
}