<?php


namespace Blog\Models;


use Blog\Entities\SocialNetwork;
use Core\Manager;

abstract class SocialNetworkManager extends Manager
{
    abstract public function getListByBlog(int $id = 1);

    abstract public function getUnique(int $id);

    abstract public function doubleExists(SocialNetwork $socialNetwork);

    abstract protected function add(SocialNetwork $socialNetwork);

    abstract protected function modify(SocialNetwork $socialNetwork);

    abstract public function delete(int $id);

    public function save(SocialNetwork $socialNetwork) {
        if ($socialNetwork->isValid()) {
            return $socialNetwork->isNew() ? $this->add($socialNetwork) : $this->modify($socialNetwork);
        } else {
            throw new \RuntimeException('Les paramètres du réseau social ne sont pas valides.');
        }
    }
}