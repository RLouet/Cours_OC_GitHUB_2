<?php


namespace Blog\Models;

use Blog\Entities\Skill;
use \PDO;


class SkillManagerPDO extends SkillManager
{
    public function getListByBlog(int $id = 1)
    {
        $sql = 'SELECT * FROM skill s WHERE s.blog_id=?';

        $stmt = $this->dao->prepare($sql);
        //$stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, '\Entities\Blog');
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute(array($id));
        $skillsList = $stmt->fetchAll();
        $stmt->closeCursor();
        //var_dump($blogData);

        return $skillsList;
    }

    public function getUnique(int $id)
    {
        $sql = 'SELECT id, value, blog_id as blogId FROM skill s WHERE s.id = :id';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Blog\Entities\Skill');
        $stmt->execute();
        $skill = $stmt->fetch();
        $stmt->closeCursor();

        return $skill;
    }

    public function doubleExists(Skill $skill)
    {
        $sql = 'SELECT id FROM skill s WHERE s.id != :id AND s.value = :value AND s.blog_id = :blog_id';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':id', (int) $skill->id(), PDO::PARAM_INT);
        $stmt->bindValue(':value', (string) $skill->value(), PDO::PARAM_STR);
        $stmt->bindValue(':blog_id', (int) $skill->blogId(), PDO::PARAM_INT);
        $stmt->execute();
        $skill = $stmt->fetch();
        $stmt->closeCursor();
        return $skill ? true : false;
    }

    protected function modify(Skill $skill)
    {
        $sql = 'UPDATE skill SET value=:value WHERE id=:id AND blog_id=:blogId';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':value', $skill->value());
        $stmt->bindValue(':id', $skill->id());
        $stmt->bindValue(':blogId', $skill->blogId());

        return $stmt->execute();
    }

    protected function add(Skill $skill)
    {
        $sql = 'INSERT INTO skill SET value=:value, blog_id=:blogId';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':value', $skill->value());
        $stmt->bindValue(':blogId', $skill->blogId());

        if ($stmt->execute()) {
            $skill->setId($this->dao->lastInsertId());
            return $skill;
        }

        return false;
    }

    public function delete(int $id)
    {
        $sql = 'DELETE FROM skill WHERE id=:id';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }
}