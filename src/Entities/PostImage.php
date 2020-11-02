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
        return !(empty($this->name) || empty($this->url));
    }

    // SETTERS //

    public function setName(string $name)
    {
        if (empty($name)) {
            $this->errors[] = self::INVALID_NAME;
        } else {
            $this->name = $name;
        }
    }

    public function setUrl(string $url)
    {
        if (empty($url) || !preg_match('/^[\da-zÀ-ÖØ-öø-ÿœŒ][\d\'a-zÀ-ÖØ-öø-ÿœŒ -.]{0,48}[\da-zÀ-ÖØ-öø-ÿœŒ]$/i', $url)) {
            $this->errors[] = self::INVALID_URL;
        } else {
            $this->url = $url;
        }
    }

    public function setBlogPostId(int $blogPostId)
    {
        if (empty($blogPostId)) {
            $this->errors[] = self::INVALID_BLOGPOSTID;
        } else {
            $this->blogPostId = $blogPostId;
        }
    }


    // GETTERS //

    public function getName() : string
    {
        return $this->name;
    }

    public function getUrl() : string
    {
        return $this->url;
    }

    public function getBlogPostId() : string
    {
        return $this->blogPostId;
    }
}