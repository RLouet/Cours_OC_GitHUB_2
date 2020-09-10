<?php


namespace Blog\Models;


use Core\Manager;

abstract class SocialNetworkManager extends Manager
{
    abstract public function getListByBlog(int $id = 1);
}