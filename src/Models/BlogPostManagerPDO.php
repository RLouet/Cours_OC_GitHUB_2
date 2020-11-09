<?php


namespace Blog\Models;

use Blog\Entities\BlogPost;
use Blog\Entities\PostImage;
use Blog\Entities\Skill;
use Blog\Entities\User;
use \PDO;
use \DateTime;


class BlogPostManagerPDO extends BlogPostManager
{
    public function getList(): array
    {
        //$sql = 'SELECT id, user_id as userId, title, edit_date as editDate, hero_id as heroId, chapo, content FROM blog_post';
        $sql = 'SELECT *, bp.id as id, user.id as user_id, pi.name AS hero_name, pi.url AS hero_url FROM blog_post bp LEFT JOIN post_image pi ON pi.id = bp.hero_id AND pi.blog_post_id = bp.id JOIN user ON user.id = bp.user_id ORDER BY bp.edit_date DESC';

        $stmt = $this->dao->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        //$stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Blog\Entities\BlogPost');
        $stmt->execute();
        $result = $stmt->fetchAll();
        $stmt->closeCursor();

        $blogPostsList = [];

        foreach ($result as $resultitem) {
            $resultitem['edit_date'] = new DateTime($resultitem['edit_date']);
            $post = new BlogPost($resultitem);
            $user = new User($resultitem);
            if ($resultitem['hero_id']) {
                $hero = new PostImage([
                    'id' => $resultitem['hero_id'],
                    'name' => $resultitem['hero_name'],
                    'url' => $resultitem['hero_url'],
                    'blog_post_id' => $resultitem['id']
                ]);
                $post->setHero($hero);
            }
            $user->setId($resultitem['user_id']);
            $post->setUser($user);
            $blogPostsList[] = $post;
        }

        //var_dump($blogPostsList);

        return $blogPostsList;
        //return array(['test']);
    }

    public function getUnique(int $id)
    {
        //$sql = 'SELECT id, user_id as userId, title, edit_date as editDate, hero_id as heroId, chapo, content FROM blog_post WHERE blog_post.id = :id';
        $sql = 'SELECT *, bp.id as id, user.id as user_id  FROM blog_post bp JOIN user ON user.id = bp.user_id WHERE bp.id = :id';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        //$stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Blog\Entities\BlogPost');
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();

        if (!$result) {
            return null;
        }

        $result['edit_date'] = new DateTime($result['edit_date']);
        $blogPost = new BlogPost($result);
        $user = new User($result);
        $user->setId($result['user_id']);
        $blogPost->setUser($user);

        $sql2 = 'SELECT * FROM post_image pi WHERE pi.blog_post_id = :bp_id';
        $stmt = $this->dao->prepare($sql2);
        $stmt->bindValue(':bp_id', (int) $blogPost->getId(), PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        $images = $stmt->fetchAll();
        $stmt->closeCursor();

        foreach ($images as $image) {
            $image = new PostImage($image);
            $blogPost->addImage($image);
            if ($image->getId() == $result['hero_id']) {
                $blogPost->setHero($image);
            }
        }

        return $blogPost;
    }

    protected function modify(BlogPost $blogPost)
    {
        $date = new DateTime();

        $sql = 'UPDATE blog_post SET title=:title, edit_date=:editDate, hero_id=:heroId, chapo=:chapo, content=:content WHERE id=:id AND user_id=:userId';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':title', $blogPost->getTitle());
        $stmt->bindValue(':editDate', $date->format('Y-m-d H:i:s'));
        if ($blogPost->getHero()) {
            $stmt->bindValue(':heroId', $blogPost->getHero()->getId());
        } else {
            $stmt->bindValue(':heroId', null);
        }
        $stmt->bindValue(':chapo', $blogPost->getChapo());
        $stmt->bindValue(':content', $blogPost->getContent());
        $stmt->bindValue(':id', $blogPost->getId());
        $stmt->bindValue(':userId', $blogPost->getUserId());

        if ($stmt->execute()) {
            return $blogPost;
        }
        return false;
    }

    protected function add(BlogPost $blogPost)
    {
        //$sql = 'INSERT INTO blog_post SET user_id=:userId, title=:title, edit_date=:editDate, hero_id=:heroId, chapo=:chapo, content=:content';
        $sql = 'INSERT INTO blog_post SET user_id=:userId, title=:title, edit_date=:editDate, hero_id=:heroId, chapo=:chapo, content=:content';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':userId', $blogPost->getUserId());
        //$stmt->bindValue(':user_id', "1");
        $stmt->bindValue(':title', $blogPost->getTitle());
        $stmt->bindValue(':editDate', $blogPost->getEditDate()->format('Y-m-d H:i:s'));
        if ($blogPost->getHero()) {
            $stmt->bindValue(':heroId', $blogPost->getHero()->getId());
        } else {
            $stmt->bindValue(':heroId', null);
        }
        //$stmt->bindValue(':heroId', "1");
        $stmt->bindValue(':chapo', $blogPost->getChapo());
        $stmt->bindValue(':content', $blogPost->getContent());

        if ($stmt->execute()) {
            $blogPost->setId($this->dao->lastInsertId());
            return $blogPost;
        }

        return false;
    }

    public function delete(int $id)
    {
        $sql = 'DELETE FROM blog_post WHERE id=:id';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }
}