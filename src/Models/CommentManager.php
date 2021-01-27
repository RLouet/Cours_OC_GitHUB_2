<?php


namespace Blog\Models;


use Blog\Entities\BlogPost;
use Blog\Entities\Comment;
use Blog\Entities\User;
use Core\Manager;

abstract class CommentManager extends Manager
{
    abstract public function getUnique(int $id);

    abstract public function getByPost(?User $user, int $blogPost, int $offset = 0);

    abstract public function getUnvalidated(int $offset = 0);

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