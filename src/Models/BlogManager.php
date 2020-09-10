<?php


namespace Blog\Models;


use Core\Manager;

abstract class BlogManager extends Manager
{
    abstract public function getData(int $id = 1);
}