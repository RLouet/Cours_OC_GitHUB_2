<?php


namespace Blog\Models;


use Blog\Entities\Skill;
use Blog\Entities\User;
use Core\Manager;
use Core\Token;

abstract class RememberedLoginManager extends Manager
{
    abstract public function save(User $user, Token $token, string $expiry): bool;

    abstract public function delete(Token $token): bool;

    abstract public function findByToken(Token $token): ?array;
}