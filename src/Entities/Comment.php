<?php


namespace Blog\Entities;


use Core\Entity;
use \DateTime;

class Comment extends Entity
{
    protected BlogPost $blogPost;
    protected \DateTime $date;
    protected User $user;
    protected string $content;
    protected bool $validated = false;

    const INVALID_BLOGPOST = 1;
    const INVALID_DATE = 2;
    const INVALID_USER = 3;
    const INVALID_CONTENT = 4;
    const INVALID_VALIDATED = 5;

    public function  isValid()
    {
        return !(empty($this->blogPost) || empty($this->date) || empty($this->user) || empty($this->content) || empty($this->validated));
    }


    // SETTERS //

    public function setBlogPost(BlogPost $blogPost): Comment
    {
        if (empty($blogPost) || !$blogPost->isValid()) {
            $this->errors[] = self::INVALID_BLOGPOST;
            return $this;
        }

        $this->blogPost = $blogPost;
        return $this;
    }

    public function setDate(DateTime $date): Comment
    {
        if (empty($date)) {
            $this->errors[] = self::INVALID_DATE;
            return $this;
        }
        $this->date = $date;
        return $this;
    }

    public function setUser(User $user): Comment
    {
        if (empty($user) || !$user->isValid()) {
            $this->errors[] = self::INVALID_USER;
            return $this;
        }
        $this->user = $user;
        return $this;
    }

    public function setContent(string $content): Comment
    {
        if (empty($content) || !preg_match('/^.{5,255}$/i', $content)) {
            $this->errors[] = self::INVALID_CONTENT;
            return $this;
        }
        $this->content = $content;
        return $this;
    }

    public function setValidated(bool $validated): Comment
    {
        if (empty($validated)) {
            $this->errors[] = self::INVALID_VALIDATED;
            return $this;
        }
        $this->validated = $validated;
        return $this;
    }


    // GETTERS //

    public function getBlogPost(): BlogPost
    {
        return $this->blogPost;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getValidated(): bool
    {
        return $this->validated;
    }
}