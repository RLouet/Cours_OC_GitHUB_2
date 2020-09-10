<?php


namespace Blog\Models;

use Blog\Entities\Blog;
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
}