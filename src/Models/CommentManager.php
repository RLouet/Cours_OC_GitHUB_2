<?php


namespace Blog\Models;


use Blog\Entities\BlogPost;
use Blog\Entities\Comment;
use Core\Manager;

abstract class CommentManager extends Manager
{
    abstract public function getUnique(int $id);

    abstract public function getByPost(BlogPost $blogPost);

    abstract public function getUnvalidated();

    abstract protected function add(Comment $comment);

    abstract protected function modify(Comment $comment);

    abstract public function delete(int $id);

    abstract public function deleteByUser(int $id);

    public function save(Comment $comment) {
        if ($comment->isValid()) {
            return $comment->isNew() ? $this->add($comment) : $this->modify($comment);
        } else {
            throw new \RuntimeException('Les paramètres du commentaire ne sont pas valides.');
        }
    }
}