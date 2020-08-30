<?php


namespace Core;

use \PDO;

class PDOFactory
{

    private static $instance = null;

    private static $pdoConnexion;

    private function __construct()
    {
        $config = Config::getInstance();

        $db = new PDO('mysql:host=' . $config->get('db_host') . ';dbname=' . $config->get('db_name'), $config->get('db_user'), $config->get('db_password'));
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        self::$pdoConnexion = $db;
    }

    public static function getInstance()
    {
        if(is_null(self::$instance))
        {
            self::$instance = new PDOFactory();
        }
        return self::$instance;
    }

    public static function getPDOConnexion()
    {
        return self::$pdoConnexion;
    }
}