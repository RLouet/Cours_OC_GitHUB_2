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
    public function getList(int $offset = 0): array
    {
        //$sql = 'SELECT id, user_id as userId, title, edit_date as editDate, hero_id as heroId, chapo, content FROM blog_post';
        $sql = '
SELECT *,
       bp.id as id, 
       user.id as user_id, 
       pi.name AS hero_name, 
       pi.url AS hero_url 
FROM blog_post bp 
    LEFT JOIN post_image pi 
        ON pi.id = bp.hero_id 
               AND pi.blog_post_id = bp.id 
    JOIN user 
        ON user.id = bp.user_id 
ORDER BY bp.edit_date DESC
LIMIT :limit 
OFFSET :offset';

        $stmt = $this->dao->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        //$stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Blog\Entities\BlogPost');
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int) $this->config->get('pagination'), PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $stmt->closeCursor();

        $blogPostsList = [];

        //var_dump($result);

        foreach ($result as $resultItem) {
            $resultItem['edit_date'] = new DateTime($resultItem['edit_date']);
            $post = new BlogPost($resultItem);
            $user = new User($resultItem);
            if ($resultItem['hero_id']) {
                $hero = new PostImage([
                    'id' => $resultItem['hero_id'],
                    'name' => $resultItem['hero_name'],
                    'url' => $resultItem['hero_url'],
                    'blog_post_id' => $resultItem['id']
                ]);
                $post->setHero($hero);
            }
            $user->setId($resultItem['user_id']);
            $post->setUser($user);
            $blogPostsList[] = $post;
        }

        return $blogPostsList;
    }

    public function getUnique(int $blogPostId)
    {
        //$sql = 'SELECT id, user_id as userId, title, edit_date as editDate, hero_id as heroId, chapo, content FROM blog_post WHERE blog_post.id = :id';
        $sql = 'SELECT *, bp.id as id, user.id as user_id  FROM blog_post bp JOIN user ON user.id = bp.user_id WHERE bp.id = :id';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':id', (int) $blogPostId, PDO::PARAM_INT);
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
        $heroId = null;
        if ($blogPost->getHero()) {
            $heroId = $blogPost->getHero()->getId();
        }

        $sql = 'UPDATE blog_post SET title=:title, edit_date=:editDate, hero_id=:heroId, chapo=:chapo, content=:content WHERE id=:id AND user_id=:userId';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':title', $blogPost->getTitle());
        $stmt->bindValue(':editDate', $date->format('Y-m-d H:i:s'));
        $stmt->bindValue(':heroId', $heroId);
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
        $heroId = null;
        if ($blogPost->getHero()) {
            $heroId = $blogPost->getHero()->getId();
        }

        $sql = 'INSERT INTO blog_post SET user_id=:userId, title=:title, edit_date=:editDate, hero_id=:heroId, chapo=:chapo, content=:content';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':userId', $blogPost->getUserId());
        $stmt->bindValue(':title', $blogPost->getTitle());
        $stmt->bindValue(':editDate', $blogPost->getEditDate()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':heroId', $heroId);
        $stmt->bindValue(':chapo', $blogPost->getChapo());
        $stmt->bindValue(':content', $blogPost->getContent());

        if ($stmt->execute()) {
            $blogPost->setId($this->dao->lastInsertId());
            return $blogPost;
        }

        return false;
    }

    public function delete(int $blogPostId)
    {
        $sql = 'DELETE FROM blog_post WHERE id=:id';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':id', $blogPostId);

        return $stmt->execute();
    }

    public function deleteByUser(int $blogPostId)
    {
        $sql = 'DELETE FROM blog_post WHERE user_id=:id';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':id', $blogPostId);

        return $stmt->execute();
    }
}