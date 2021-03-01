<?php


namespace Blog\Models;

use Blog\Entities\BlogPost;
use Blog\Entities\User;
use Core\Token;
use \PDO;
use \DateTime;


class UserManagerPDO extends UserManager
{
    public function getList(?string $role = null): array
    {
        $sql = 'SELECT * FROM user';

        if ($role) {
            $sql .= ' WHERE role = :role';
        }

        $stmt = $this->dao->prepare($sql);

        if ($role) {
            $stmt->bindValue(':role', $role, PDO::PARAM_STR);
        }
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $stmt->closeCursor();

        $userList = [];

        foreach ($result as $resultItem) {
            $resultItem['registration_date'] = new DateTime($result['registration_date']);
            $user = new User($resultItem);
            $userList[] = $user;
        }

        return $userList;
    }

    public function count(?array $roles = null): array
    {
        $sql = 'SELECT ';
        if (!$roles) {
            $roles = ['ROLE_USER', 'ROLE_ADMIN'];
        }
        foreach ($roles as $role) {
            $sql .= '(SELECT COUNT(*) FROM user WHERE role="' . $role . '") AS "' . $role . '", ' ;
        }
        $sql .= '(SELECT COUNT(*) FROM user) AS "all"';
        $stmt = $this->dao->query($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();

        return $result;
    }

    public function findById(int $userId): ?User
    {
        $sql = 'SELECT * FROM user WHERE id =:id';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();

        if ($result) {
            $result['registration_date'] = new DateTime($result['registration_date']);
            $user = new User($result);
            if ($user->isValid()){
                return $user;
            }
        }
        return null;
    }

    public function findByPasswordToken(string $token): ?User
    {
        $token = new Token($token);
        $hashedToken = $token->getHash();

        $sql = 'SELECT * FROM user WHERE password_reset_hash=:password_reset_hash';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':password_reset_hash', $hashedToken, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();

        if (!$result) {
            return null;
        }

        $result['registration_date'] = new DateTime($result['registration_date']);
        $result['password_reset_expiry'] = new DateTime($result['password_reset_expires_at']);
        $user = new User($result);

        if ($user->isValid()){
            return $user;
        }
        return null;
    }

    public function activate(string $token): bool
    {
        $token = new Token($token);
        $hashedToken = $token->getHash();

        $sql = 'UPDATE user SET enabled = 1, activation_hash = NULL WHERE activation_hash = :hashed_token';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':hashed_token', $hashedToken, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function changeEmail(string $token): bool
    {
        $token = new Token($token);
        $hashedToken = $token->getHash();

        $sql = 'UPDATE user SET activation_hash = NULL, email = new_email, new_email = NULL WHERE activation_hash = :hashed_token';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':hashed_token', $hashedToken, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function findByEmail(string $email): ?User
    {
        $sql = 'SELECT * FROM user WHERE email =:email';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':email', (string) $email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();

        if ($result) {
            $result['registration_date'] = new DateTime($result['registration_date']);
            return new User($result);
        }
        return null;
    }

    public function mailExists(string $email, ?int $ignoreId = null): ?User
    {
        $user =  $this->findByEmail($email);

        if ($user) {
            if ($user->getId() != $ignoreId) {
                return $user;
            }
        }
        return null;
    }

    public function UserExists(string $username, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT id FROM user WHERE username =:username';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':username', (string) $username, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();

        if ($result) {
            $user = new User($result);
            if ($user->getId() != $ignoreId) {
                return true;
            }
        }
        return false;
    }

    public function startPasswordReset(User $user): ?User
    {
        $sql = 'UPDATE user SET password_reset_hash=:password_reset_hash, password_reset_expires_at=:password_reset_expires_at WHERE id=:id';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':password_reset_hash', $user->getPasswordResetHash(), PDO::PARAM_STR);
        $stmt->bindValue(':password_reset_expires_at', $user->getPasswordResetExpiry()->format('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt->bindValue(':id', $user->getId(), PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $user;
        }
        return null;
    }

    public function resetPassword(User $user): ?User
    {
        $sql = 'UPDATE user SET password=:password_hash, password_reset_hash = NULL, password_reset_expires_at = NULL WHERE id=:id';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':password_hash', $user->getPassword(), PDO::PARAM_STR);
        //$stmt->bindValue(':id', $user->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':id', $user->getId(), PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $user;
        }
        return null;
    }

    protected function add(User $user): ?User
    {
        $sql = '
INSERT INTO 
    user 
SET 
    username=:username, 
    lastname=:lastname, 
    firstname=:firstname, 
    email=:email, 
    password=:password, 
    role=:role, 
    activation_hash=:activation_hash';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':username', $user->getUsername(), PDO::PARAM_STR);
        $stmt->bindValue(':lastname', $user->getLastname(), PDO::PARAM_STR);
        $stmt->bindValue(':firstname', $user->getFirstname(), PDO::PARAM_STR);
        $stmt->bindValue(':email', $user->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(':password', $user->getPassword(), PDO::PARAM_STR);
        $stmt->bindValue(':role', "ROLE_USER", PDO::PARAM_STR);
        $stmt->bindValue(':activation_hash', $user->getActivationHash(), PDO::PARAM_STR);

        if ($stmt->execute()) {
            $user->setId($this->dao->lastInsertId());
            return $user;
        }
        return null;
    }

    protected function modify(User $user): ?User
    {
        $sql = '
UPDATE 
    user 
SET 
    username = :username, 
    lastname = :lastname, 
    firstname = :firstname, 
    email = :email, 
    password = :password, 
    role = :role, 
    activation_hash = :activation_hash, 
    new_email = :new_email, 
    banished = :banished, 
    registration_date = :registration_date
WHERE 
      id=:id';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':id', $user->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':username', $user->getUsername(), PDO::PARAM_STR);
        $stmt->bindValue(':lastname', $user->getLastname(), PDO::PARAM_STR);
        $stmt->bindValue(':firstname', $user->getFirstname(), PDO::PARAM_STR);
        $stmt->bindValue(':email', $user->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(':password', $user->getPassword(), PDO::PARAM_STR);
        $stmt->bindValue(':role', $user->getRole(), PDO::PARAM_STR);
        $stmt->bindValue(':activation_hash', $user->getActivationHash(), PDO::PARAM_STR);
        $stmt->bindValue(':registration_date', $user->getRegistrationDate()->format('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt->bindValue(':new_email', $user->getNewEmail(), PDO::PARAM_STR);
        $stmt->bindValue(':banished', $user->getBanished(), PDO::PARAM_BOOL);

        if ($stmt->execute()) {
            return $user;
        }
        return null;
    }

    public function delete(int $userId): bool
    {
        $sql = 'DELETE FROM user WHERE id=:id';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}