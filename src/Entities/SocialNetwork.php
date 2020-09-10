<?php


namespace Blog\Entities;


use Core\Entity;

class SocialNetwork extends Entity
{
    protected $blogId,
        $name,
        $logo,
        $url;

    const INVALID_BLOG_ID = 1;
    const INVALID_NAME = 2;
    const INVALID_LOGO = 3;
    const INVALID_URL = 4;

    public function  isValid()
    {
        return !(empty($this->blogId) || empty($this->name) || empty($this->logo) || empty($this->url));
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

    public function setName(string $name)
    {
        if (empty($name) || !preg_match('/^[a-z][a-z- ]{0,48}[a-z]$/i', $name)) {
            $this->errors[] = self::INVALID_NAME;
        } else {
            $this->name = $name;
        }
    }

    public function setLogo(string $logo)
    {
        if (empty($logo)) {
            $this->errors[] = self::INVALID_LOGO;
        } else {
            $this->logo = $logo;
        }
    }

    public function setUrl(string $url)
    {
        if (empty($url)) {
            $this->errors[] = self::INVALID_URL;
        } else {
            $this->url = $url;
        }
    }


    // GETTERS //

    public function blogId()
    {
        return $this->blogId;
    }

    public function name()
    {
        return $this->name;
    }

    public function logo()
    {
        return $this->logo;
    }

    public function url()
    {
        return $this->url;
    }
}