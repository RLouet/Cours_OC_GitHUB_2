<?php


namespace Blog\Models;

use Blog\Entities\User;
use \PDO;


class UserManagerPDO extends UserManager
{
    public function getList(): array
    {
        return [];
    }

    public function getUnique(string $email)
    {
        $sql = 'SELECT * FROM user WHERE user.email =:email';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();

        $user = new User($result);

        if ($user->isValid()){
            return $user;
        }
        return false;
    }

    public function findByEmail(string $email)
    {
        $sql = 'SELECT * FROM user WHERE email =:email';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':email', (string) $email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();
        return $result ? new User($result) : false;
    }

    public function mailExists(string $email) {
        return $this->findByEmail($email) ? true : false;
    }

    public function UserExists(string $username)
    {
        $sql = 'SELECT id FROM user WHERE username =:username';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':username', (string) $username, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();
        return $result ? true : false;
    }

    protected function modify(User $user)
    {
        $sql = 'UPDATE user SET username=:username, lastname=:lastname, firstname=:firstname, email=:email, password=:password, role=:role WHERE id=:id';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':username', $user->getUsername(), PDO::PARAM_STR);
        $stmt->bindValue(':lastname', $user->getLastname(), PDO::PARAM_STR);
        $stmt->bindValue(':firstname', $user->getFirstname(), PDO::PARAM_STR);
        $stmt->bindValue(':email', $user->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(':password', $user->getPassword(), PDO::PARAM_STR);
        $stmt->bindValue(':role', $user->getRole(), PDO::PARAM_STR);
        $stmt->bindValue(':id', $user->id(), PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $user;
        }
        return false;
    }

    protected function add(User $user)
    {
        $sql = 'INSERT INTO user SET username=:username, lastname=:lastname, firstname=:firstname, email=:email, password=:password, role=:role';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':username', $user->getUsername(), PDO::PARAM_STR);
        $stmt->bindValue(':lastname', $user->getLastname(), PDO::PARAM_STR);
        $stmt->bindValue(':firstname', $user->getFirstname(), PDO::PARAM_STR);
        $stmt->bindValue(':email', $user->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(':password', $user->getPassword(), PDO::PARAM_STR);
        $stmt->bindValue(':role', $user->getRole(), PDO::PARAM_STR);

        if ($stmt->execute()) {
            $user->setId($this->dao->lastInsertId());
            return $user;
        }
        return false;
    }

    public function delete(int $id)
    {
        $sql = 'DELETE FROM user WHERE id=:id';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}