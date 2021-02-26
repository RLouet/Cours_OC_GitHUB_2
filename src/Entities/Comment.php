<?php


namespace Blog\Entities;


use Core\Entity;
use \DateTime;
use JsonSerializable;

class Comment extends Entity implements JsonSerializable
{
    protected BlogPost $blogPost;
    protected DateTime $date;
    protected User $user;
    protected string $content = "";
    protected bool $validated = false;

    const INVALID_BLOGPOST = 1;
    const INVALID_DATE = 2;
    const INVALID_USER = 3;
    const INVALID_CONTENT = 4;
    const INVALID_VALIDATED = 5;

    public function  isValid()
    {
        return !(empty($this->blogPost) || empty($this->user) || empty($this->content) || !is_bool($this->validated) || !empty($this->errors));
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'date' => $this->getDate()->format('d/m/Y à H:i:s'),
            'content' => nl2br($this->getContent()),
            'validated' => $this->getValidated(),
            'username' => $this->getUser()->getUsername(),
            'userId' => $this->getUser()->getId(),
            'postId' => isset($this->blogPost)?$this->getBlogPost()->getId():null,
            'postTitle' => isset($this->blogPost)?$this->getBlogPost()->getTitle():null
        ];
    }

    // SETTERS //

    public function setBlogPost(BlogPost $blogPost): Comment
    {
        if (empty($blogPost)) {
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
        if (empty($user)) {
            $this->errors[] = self::INVALID_USER;
            return $this;
        }
        $this->user = $user;
        return $this;
    }

    public function setContent(string $content): Comment
    {
        $this->content = $content;
        if (empty($content) || !preg_match('/^.{5,255}$/im', $content)) {
            $this->errors[] = self::INVALID_CONTENT;
            return $this;
        }
        return $this;
    }

    public function setValidated(bool $validated): Comment
    {
        if (!is_bool($validated)) {
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
        return (bool) $this->validated;
    }
}