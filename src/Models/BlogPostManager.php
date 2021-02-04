<?php


namespace Blog\Models;


use Blog\Entities\BlogPost;
use Core\Manager;

abstract class BlogPostManager extends Manager
{
    abstract public function getUnique(int $id);

    abstract public function getList(int $offset = 0);

    abstract protected function add(BlogPost $blogPost);

    abstract protected function modify(BlogPost $blogPost);

    abstract public function delete(int $id);

    abstract public function deleteByUser(int $id);

    public function save(BlogPost $blogPost) {
        if ($blogPost->isValid()) {
            return $blogPost->isNew() ? $this->add($blogPost) : $this->modify($blogPost);
        }
        throw new \RuntimeException('Les paramètres du post ne sont pas valides.');

    }
}