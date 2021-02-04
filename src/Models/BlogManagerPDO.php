<?php


namespace Blog\Models;

use Blog\Entities\Blog;
use Blog\Entities\Skill;
use Blog\Entities\SocialNetwork;
use PDO;


class BlogManagerPDO extends BlogManager
{
    public function getData(int $blogId = 1)
    {
        $sql = 'SELECT * FROM blog  WHERE id=?';

        $stmt = $this->dao->prepare($sql);
        //$stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, '\Blog\Entities\Blog');
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute(array($blogId));
        $blogData = $stmt->fetch();
        $stmt->closeCursor();

        $blog = new Blog($blogData);

        $sqlSocial = 'SELECT * FROM social_network s WHERE s.blog_id=?';
        $stmt = $this->dao->prepare($sqlSocial);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute(array($blogId));
        $socialNetworksList = $stmt->fetchAll();

        foreach ($socialNetworksList as $socialNetwork) {
            $socialNetwork = new SocialNetwork($socialNetwork);
            $blog->addSocialNetwork($socialNetwork);
        }

        $sqlSkill = 'SELECT * FROM skill s WHERE s.blog_id=?';
        $stmt = $this->dao->prepare($sqlSkill);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute(array($blogId));
        $skillsList = $stmt->fetchAll();

        foreach ($skillsList as $skill) {
            $skill = new Skill($skill);
            $blog->addSkill($skill);
        }

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

        $stmt->bindValue(':lastname', $blog->getLastname());
        $stmt->bindValue(':firstname', $blog->getfirstname());
        $stmt->bindValue(':email', $blog->getEmail());
        $stmt->bindValue(':phone', $blog->getPhone());
        $stmt->bindValue(':logo', $blog->getLogo());
        $stmt->bindValue(':teaser_phrase', $blog->getTeaserPhrase());
        $stmt->bindValue(':contact_mail', $blog->getContactMail());
        $stmt->bindValue(':cv', $blog->getCv());
        $stmt->bindValue(':id', $blog->getId());

        $stmt->execute();

    }
}