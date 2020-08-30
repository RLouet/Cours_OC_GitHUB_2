<?php


namespace Core;


class PDOFactory
{
    public static function getPDOConnexion()
    {
        $config = new Config();

        $db = new \PDO('mysql:host=' . $config->get('db_host') . ';dbname=' . $config->get('db_name'), $config->get('db_user'), $config->get('db_password'));
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $db;
    }
}