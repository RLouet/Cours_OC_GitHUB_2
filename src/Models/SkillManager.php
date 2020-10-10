<?php


namespace Blog\Models;


use Core\Manager;

abstract class SkillManager extends Manager
{
    abstract public function getListByBlog(int $id = 1);

    abstract public function getUnique(int $id);
}