<?php


namespace Blog\Models;


use Blog\Entities\Blog;
use Core\Manager;

abstract class BlogManager extends Manager
{
    abstract public function getData(int $id = 1);

    abstract protected function add(Blog $blog);

    abstract protected function modify(Blog $blog);

    public function save(Blog $blog) {
        if ($blog->isValid()) {
            $blog->isNew() ? $this->add($blog) : $this->modify($blog);
        } else {
            throw new \RuntimeException('Les paramètres du blog ne sont pas valides.');
        }
    }
}