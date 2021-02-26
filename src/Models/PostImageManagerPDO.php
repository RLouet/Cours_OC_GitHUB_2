<?php


namespace Blog\Models;

use Blog\Entities\PostImage;
use \PDO;

class PostImageManagerPDO extends PostImageManager
{
    protected function modify(PostImage $postImage)
    {
        $sql = 'UPDATE post_image SET name=:name, url=:url WHERE id=:id AND blog_post_id=:blogPostId';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':name', $postImage->getName());
        $stmt->bindValue(':url', $postImage->getUrl());
        $stmt->bindValue(':id', $postImage->getId());
        $stmt->bindValue(':blogPostId', $postImage->getBlogPostId());

        if ($stmt->execute()) {
            return $postImage;
        }
        return false;
    }

    protected function add(PostImage $postImage)
    {
        $sql = 'INSERT INTO post_image SET name=:name, url=:url, blog_post_id=:blogPostId';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':name', $postImage->getName());
        $stmt->bindValue(':url', $postImage->getUrl());
        $stmt->bindValue(':blogPostId', $postImage->getBlogPostId());

        if ($stmt->execute()) {
            $postImage->setId($this->dao->lastInsertId());
            return $postImage;
        }

        return false;
    }

    public function delete(int $postImageId)
    {
        $sql = 'DELETE FROM post_image WHERE id=:id';

        $stmt = $this->dao->prepare($sql);

        $stmt->bindValue(':id', $postImageId);

        return $stmt->execute();
    }
}