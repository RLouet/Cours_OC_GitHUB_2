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
        $sql = 'SELECT * FROM skill s WHERE s.id = :id';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, '\Entities\Skill');

        if ($skill = $stmt->fetch()) {
            return $skill;
        }

        return null;
    }

    protected function modify(Skill $skill)
    {

    }

    protected function add(Skill $skill)
    {

    }
}