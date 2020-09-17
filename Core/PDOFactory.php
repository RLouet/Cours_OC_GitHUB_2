<?php


namespace Core;

use PDO;

class PDOFactory
{

    private static $pdoConnexion = null;
    protected Config $config;

    private function __construct()
    {
        $this->config = Config::getInstance();
    }

    public static function getPDOConnexion()
    {
        if (is_null(self::$pdoConnexion)) {
            self::$pdoConnexion = (new PDOFactory())->getDb();
        }
        return self::$pdoConnexion;
    }

    public function getDb()
    {
        $db = new PDO('mysql:host=' . $this->config->get('db_host') . ';dbname=' . $this->config->get('db_name'), $this->config->get('db_user'), $this->config->get('db_password'));
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    }
}