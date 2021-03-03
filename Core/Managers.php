<?php


namespace Core;

class Managers
{
    protected string $api;
    protected $dao = null;
    protected array $managers = [];

    public function __construct($api, $dao)
    {
        $this->api = $api;
        $this->dao = $dao;
    }

    public function getManagerOf($entity)
    {
        if (!is_string($entity) || empty($entity))
        {
            throw new \InvalidArgumentException('L\'entité spécifiée est invalide');
        }

        if (!isset($this->managers[$entity]))
        {
            $manager = '\\Blog\\Models\\'.ucfirst($entity).'Manager'.$this->api;

            $this->managers[$entity] = new $manager($this->dao);
        }

        return $this->managers[$entity];
    }
}