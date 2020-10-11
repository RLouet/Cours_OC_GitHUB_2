<?php


namespace Blog\Models;


use Blog\Entities\Skill;
use Core\Manager;

abstract class SkillManager extends Manager
{
    abstract public function getListByBlog(int $id = 1);

    abstract public function getUnique(int $id);

    abstract public function doubleExists(Skill $skill);

    abstract protected function add(Skill $skill);

    abstract protected function modify(Skill $skill);

    abstract public function delete(int $id);

    public function save(Skill $skill) {
        if ($skill->isValid()) {
            return $skill->isNew() ? $this->add($skill) : $this->modify($skill);
        } else {
            throw new \RuntimeException('Les paramètres du skill ne sont pas valides.');
        }
    }
}