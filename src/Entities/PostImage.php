<?php


namespace Blog\Entities;


use Core\Entity;

class PostImage extends Entity
{
    protected string $name;
    protected string $url;
    protected int $blogPostId;

    const INVALID_NAME = 1;
    const INVALID_URL = 2;
    const INVALID_BLOGPOSTID = 3;

    public function  isValid()
    {
        return !(empty($this->name) || empty($this->url) || empty($this->blogPostId) || !empty($this->errors));
    }

    // SETTERS //

    public function setName(string $name): PostImage
    {
        if (empty($name) || !preg_match('/^[\da-zÀ-ÖØ-öø-ÿœŒ][\da-zÀ-ÖØ-öø-ÿœŒ\- ]{0,62}[\da-zÀ-ÖØ-öø-ÿœŒ]$/i', $name)) {
            $this->errors[] = self::INVALID_NAME;
            return $this;
        }
        $this->name = $name;
        return $this;
    }

    public function setUrl(string $url): PostImage
    {
        if (empty($url) || !preg_match('/^[-&%_:?\/=.\da-z]{5,128}$/i', $url)) {
            $this->errors[] = self::INVALID_URL;
            return $this;
        }
        $this->url = $url;
        return $this;
    }

    public function setBlogPostId(int $blogPostId): PostImage
    {
        if (empty($blogPostId)) {
            $this->errors[] = self::INVALID_BLOGPOSTID;
            return $this;
        }
        $this->blogPostId = $blogPostId;
        return $this;
    }


    // GETTERS //

    public function getName(): string
    {
        return isset($this->name)?$this->name:"";
    }

    public function getUrl(): string
    {
        return isset($this->url)?$this->url:"";
    }

    public function getBlogPostId(): int
    {
        return $this->blogPostId;
    }
}