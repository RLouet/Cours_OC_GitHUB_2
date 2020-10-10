<?php


namespace Blog\Entities;


use Core\Entity;

class Skill extends Entity
{
    protected $blogId,
        $value;

    const INVALID_BLOG_ID = 1;
    const INVALID_VALUE = 2;

    public function  isValid()
    {
        return !(empty($this->blogId) || empty($this->value));
    }


    // SETTERS //

    public function setBlogId(int $blogId)
    {
        if (empty($blogId)) {
            $this->errors[] = self::INVALID_BLOG_ID;
        } else {
            $this->blogId = $blogId;
        }
    }

    public function setValue(string $value)
    {
        if (empty($value) || !preg_match('/^[a-z][a-z- ]{0,48}[a-z]$/i', $value)) {
            $this->errors[] = self::INVALID_VALUE;
        } else {
            $this->value = $value;
        }
    }


    // GETTERS //

    public function blogId()
    {
        return $this->blogId;
    }

    public function value()
    {
        return $this->value;
    }
}