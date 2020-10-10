<?php


namespace Blog\Models;

use Blog\Entities\SocialNetwork;
use \PDO;


class SocialNetworkManagerPDO extends SocialNetworkManager
{
    public function getListByBlog(int $id = 1)
    {
        $sql = 'SELECT * FROM social_network s WHERE s.blog_id=?';

        $stmt = $this->dao->prepare($sql);
        //$stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, '\Entities\Blog');
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute(array($id));
        $socialNetworksList = $stmt->fetchAll();
        $stmt->closeCursor();
        //var_dump($blogData);

        return $socialNetworksList;
    }

    public function getUnique(int $id)
    {
        $sql = 'SELECT id, blog_id as blogId, name, logo, url FROM social_network s WHERE s.id = :id';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, 'Blog\Entities\SocialNetwork');
        $stmt->execute();
        $socialNetwork = $stmt->fetch();
        $stmt->closeCursor();
        return $socialNetwork;
    }

    public function doubleExists(SocialNetwork $socialNetwork)
    {
        $sql = 'SELECT id FROM social_network s WHERE s.id != :id AND s.name = :name AND s.blog_id = :blog_id';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':id', (int) $socialNetwork->id(), PDO::PARAM_INT);
        $stmt->bindValue(':name', (string) $socialNetwork->name(), PDO::PARAM_STR);
        $stmt->bindValue(':blog_id', (int) $socialNetwork->blogId(), PDO::PARAM_INT);
        $stmt->execute();
        $socialNetwork = $stmt->fetch();
        $stmt->closeCursor();
        return $socialNetwork ? true : false;
    }

    protected function modify(SocialNetwork $socialNetwork)
    {
        $sql = 'UPDATE social_network SET name=:name, logo=:logo, url=:url WHERE id=:id AND blog_id=:blogId';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':name', $socialNetwork->name());
        $stmt->bindValue(':logo', $socialNetwork->logo());
        $stmt->bindValue(':url', $socialNetwork->url());
        $stmt->bindValue(':id', $socialNetwork->id());
        $stmt->bindValue(':blogId', $socialNetwork->blogId());

        return $stmt->execute();
    }

    protected function add(SocialNetwork $socialNetwork)
    {
        $sql = 'INSERT INTO social_network SET name=:name, logo=:logo, url=:url, blog_id=:blogId';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':name', $socialNetwork->name());
        $stmt->bindValue(':logo', $socialNetwork->logo());
        $stmt->bindValue(':url', $socialNetwork->url());
        $stmt->bindValue(':blogId', $socialNetwork->blogId());

        if ($stmt->execute()) {
            $socialNetwork->setId($this->dao->lastInsertId());
            return $socialNetwork;
        }

        return false;
    }

    public function delete(int $id)
    {
        $sql = 'DELETE FROM social_network WHERE id=:id';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }
}