<?php


namespace Blog\Models;

use Blog\Entities\Blog;
use Blog\Entities\SocialNetwork;
use \PDO;


class BlogManagerPDO extends BlogManager
{
    public function getData(int $id = 1)
    {
        $sql = 'SELECT * FROM blog  WHERE id=?';

        $stmt = $this->dao->prepare($sql);
        //$stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, '\Blog\Entities\Blog');
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute(array($id));
        $blogData = $stmt->fetch();
        $stmt->closeCursor();

        $blog = new Blog($blogData);
        //var_dump($blogData);

        $sqlSocial = 'SELECT * FROM social_network s WHERE s.blog_id=?';
        $stmt = $this->dao->prepare($sqlSocial);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute(array($id));
        $socialNetworksList = $stmt->fetchAll();



        foreach ($socialNetworksList as $socialNetwork) {
            $socialNetwork = new SocialNetwork($socialNetwork);
            $blog->addSocialNetwork($socialNetwork);
        }

        //var_dump($blog);

        return $blog;
    }
}