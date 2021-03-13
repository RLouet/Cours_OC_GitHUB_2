<?php


namespace Blog\Models;

use Blog\Entities\Skill;
use Blog\Entities\User;
use Core\Token;
use \PDO;


class RememberedLoginManagerPDO extends RememberedLoginManager
{
    public function save(User $user, Token $token, string $expiry): bool
    {
        $hashedToken = $token->getHash();

        $sql = 'INSERT INTO remembered_login SET token_hash=:token_hash, user_id=:user_id, expires_at=:expires_at';
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':token_hash', $hashedToken, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $user->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiry), PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function findByToken(Token $token): ?array
    {
        $hashedToken = $token->getHash();

        $sql = 'SELECT * FROM remembered_login WHERE token_hash =:token_hash';
        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':token_hash', $hashedToken, PDO::PARAM_STR);

        $stmt->execute();
        $return = $stmt->fetch();
        $stmt->closeCursor();

        return $return?$return:null;
    }

    public function delete(Token $token): bool
    {
        $hashedToken = $token->getHash();

        $sql = 'DELETE FROM remembered_login WHERE token_hash=:token_hash';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':token_hash', $hashedToken, PDO::PARAM_STR);

        return $stmt->execute();
    }
}