<?php


namespace Blog\Models;

use Blog\Entities\BlogPost;
use Blog\Entities\Comment;
use Blog\Entities\PostImage;
use Blog\Entities\Skill;
use Blog\Entities\User;
use \PDO;
use \DateTime;


class CommentManagerPDO extends CommentManager
{
    public function getUnique(int $id)
    {
        $sql = '
SELECT *, 
       comment.id as id, 
       user.id as user_id, 
       bp.id as post_id, 
       comment.content as content, 
       bp.content as post_content, 
       comment.user_id as user_id, 
       bp.user_id as post_user 
FROM comment 
    JOIN user 
        ON user.id = comment.user_id 
    JOIN blog_post bp 
        ON comment.blog_post_id = bp.id 
WHERE comment.id = :id';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();

        if (!$result) {
            return null;
        }

        $result['edit_date'] = new DateTime($result['edit_date']);
        $result['date'] = new DateTime($result['date']);

        $comment = new Comment($result);

        $blogPost = new BlogPost($result);
        $blogPost->setId($result['post_id']);
        $blogPost->setContent($result['post_content']);
        $blogPost->setUserId($result['post_user']);

        $user = new User($result);
        $user->setId($result['user_id']);

        $comment->setUser($user);
        $comment->setBlogPost($blogPost);

        return $comment;
    }

    public function getByPost(BlogPost $blogPost)
    {
        $sql = '
SELECT comment.*, user.id as user_id , user.username
FROM comment 
    JOIN user 
        ON user.id = comment.user_id 
WHERE comment.blog_post_id = :id 
ORDER BY comment.date DESC 
';

        $stmt = $this->dao->prepare($sql);
        $stmt->bindValue(':id', (int) $blogPost->getId(), PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $stmt->closeCursor();

        $commentsList = [];

        foreach ($result as $resultItem) {
            $resultItem['date'] = new DateTime($resultItem['date']);
            $comment = new Comment($resultItem);
            //$comment->setBlogPost($blogPost);

            $user = new User($resultItem);
            $user->setId($resultItem['user_id']);

            $comment->setUser($user);
            //$comment->setBlogPost($blogPost);
            $commentsList[] = $comment;
        }

        return $commentsList;
    }

    public function getUnvalidated()
    {
        $sql = '
SELECT 
       comment.*, 
       user.username 
           as user_username, 
       bp.id 
           as post_id,  
       bp.title 
           as post_title
FROM comment 
    JOIN user 
        ON user.id = comment.user_id 
    JOIN blog_post bp 
        ON comment.blog_post_id = bp.id 
WHERE comment.validated = 0';

        $stmt = $this->dao->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $stmt->closeCursor();

        $commentsList = [];

        foreach ($result as $resultItem) {
            $resultItem['date'] = new DateTime($resultItem['date']);

            $comment = new Comment($resultItem);

            $blogPost = new BlogPost();
            $blogPost->setId($resultItem['post_id']);
            $blogPost->setTitle($resultItem['post_title']);

            $user = new User();
            $user->setUsername($resultItem['user_username']);

            $comment->setUser($user);
            $comment->setBlogPost($blogPost);
            $commentsList[] = $comment;
        }

        return $commentsList;
    }

    protected function modify(Comment $comment)
    {

        $sql = 'UPDATE comment SET content=:content, validated=:validated WHERE id=:id';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':content', $comment->getContent());
        $stmt->bindValue(':validated', $comment->getValidated(), PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $comment->getId());

        if ($stmt->execute()) {
            return $comment;
        }
        return false;
    }

    protected function add(Comment $comment)
    {
        $sql = 'INSERT INTO comment SET user_id=:userId, content=:content, blog_post_id=:postId, validated=:validated';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':content', $comment->getContent());
        $stmt->bindValue(':userId', $comment->getUser()->getId());
        $stmt->bindValue(':postId', $comment->getBlogPost()->getId());
        $stmt->bindValue(':validated', $comment->getValidated(), PDO::PARAM_BOOL);

        if ($stmt->execute()) {
            $comment->setId($this->dao->lastInsertId());
            return $comment;
        }

        return false;
    }

    public function delete(int $id)
    {
        $sql = 'DELETE FROM comment WHERE id=:id';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }

    public function deleteByUser(int $id)
    {
        $sql = 'DELETE FROM comment WHERE user_id=:id';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }
}