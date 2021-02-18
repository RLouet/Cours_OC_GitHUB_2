<?php


namespace Core;

use PDO;

class PDOFactory
{

    private static PDO $pdoConnexion;
    protected Config $config;

    private function __construct()
    {
        $this->config = Config::getInstance();
    }

    public static function getPDOConnexion()
    {
        if (!isset(self::$pdoConnexion)) {
            self::$pdoConnexion = (new self)->getDb();
        }
        return self::$pdoConnexion;
    }

    private function getDb()
    {
        $db = new PDO('mysql:host=' . $this->config->get('db_host') . ';dbname=' . $this->config->get('db_name'), $this->config->get('db_user'), $this->config->get('db_password'));
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec('SET NAMES utf8');
        return $db;
    }
}