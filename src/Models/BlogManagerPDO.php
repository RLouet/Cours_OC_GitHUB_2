<?php


namespace Blog\Models;

use Blog\Entities\Blog;
use Blog\Entities\SocialNetwork;
use PDO;


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

    protected function add(Blog $blog)
    {
        // TODO: Implement add() method.
    }

    protected function modify(Blog $blog)
    {
        $sql = 'UPDATE blog SET lastname=:lastname, firstname=:firstname, email=:email, phone=:phone, logo=:logo, teaser_phrase=:teaser_phrase, contact_mail=:contact_mail, cv=:cv WHERE id=:id';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':lastname', $blog->lastname());
        $stmt->bindValue(':firstname', $blog->firstname());
        $stmt->bindValue(':email', $blog->email());
        $stmt->bindValue(':phone', $blog->phone());
        $stmt->bindValue(':logo', $blog->logo());
        $stmt->bindValue(':teaser_phrase', $blog->teaserPhrase());
        $stmt->bindValue(':contact_mail', $blog->contactMail());
        $stmt->bindValue(':cv', $blog->cv());
        $stmt->bindValue(':id', $blog->id());

        $stmt->execute();

    }
}