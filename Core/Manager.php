<?php


namespace Core;


abstract class Manager
{
    protected $dao;
    protected $config;

    public function __construct($dao)
    {
        $this->dao = $dao;
        $this->config = Config::getInstance();
    }
}