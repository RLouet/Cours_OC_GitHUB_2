<?php


namespace Blog\Entities;


use Core\Entity;
use JsonSerializable;

class SocialNetwork extends Entity implements JsonSerializable
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

    public function jsonSerialize()
    {
        return [
            'id' => $this->id(),
            'name' => $this->name(),
            'url' => $this->url(),
            'logo' => $this->logo(),
            'errors' => $this->errors(),
            'blogId' => $this->blogId(),
        ];
    }


    // SETTERS //

    public function setBlogId(int $blogId): SocialNetwork
    {
        if (empty($blogId)) {
            $this->errors[] = self::INVALID_BLOG_ID;
            return $this;
        }
        $this->blogId = $blogId;
        return $this;
    }

    public function setName(string $name)
    {
        if (empty($name) || !preg_match('/^[\da-zÀ-ÖØ-öø-ÿœŒ][\da-zÀ-ÖØ-öø-ÿœŒ\- ]{0,48}[\da-zÀ-ÖØ-öø-ÿœŒ]$/i', $name)) {
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
        if (empty($url) || !preg_match('/^[-&%_:?\/=.\da-z]{5,50}$/i', $url)) {
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