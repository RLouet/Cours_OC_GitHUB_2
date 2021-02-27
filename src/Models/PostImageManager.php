<?php


namespace Blog\Models;

use Blog\Entities\PostImage;
use Core\Manager;

abstract class PostImageManager extends Manager
{
    abstract protected function add(PostImage $postImage);

    abstract protected function modify(PostImage $postImage);

    abstract public function delete(int $postImageId);

    public function save(PostImage $postImage) {
        if ($postImage->isValid()) {
            return $postImage->isNew() ? $this->add($postImage) : $this->modify($postImage);
        }
        throw new \RuntimeException("Les paramètres de l'image ne sont pas valides.");
    }
}