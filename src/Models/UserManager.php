<?php


namespace Blog\Models;

use Blog\Entities\PostImage;
use Blog\Entities\User;
use Core\Manager;

abstract class UserManager extends Manager
{
    abstract public function getList();

    abstract public function findById(int $id);

    abstract public function findByEmail(string $email);

    abstract public function findByPasswordToken(string $token);

    abstract public function activate(string $token);

    abstract public function mailExists(string $email);

    abstract public function userExists(string $username);

    abstract protected function modify(User $user);

    abstract public function startPasswordReset(User $user);

    abstract public function resetPassword(User $user);

    abstract protected function add(User $user);

    abstract public function delete(int $id);

    public function save(User $user) {
        if ($user->isValid()) {
            return $user->isNew() ? $this->add($user) : $this->modify($user);
        } else {
            throw new \RuntimeException("Les paramètres de l'utilisateur ne sont pas valides.");
        }
    }
}