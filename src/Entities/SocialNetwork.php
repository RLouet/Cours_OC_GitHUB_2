<?php


namespace Blog\Entities;


use Core\Entity;
use JsonSerializable;

class SocialNetwork extends Entity implements JsonSerializable
{

    protected int $blogId;
    protected string $name;
    protected string $logo;
    protected string $url;

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
            'id' => $this->getId(),
            'name' => $this->getName(),
            'url' => $this->getUrl(),
            'logo' => $this->getLogo(),
            'errors' => $this->getErrors(),
            'blogId' => $this->getBlogId(),
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

    public function setName(string $name): SocialNetwork
    {
        if (empty($name) || !preg_match('/^[\da-zÀ-ÖØ-öø-ÿœŒ][\da-zÀ-ÖØ-öø-ÿœŒ\- ]{0,48}[\da-zÀ-ÖØ-öø-ÿœŒ]$/i', $name)) {
            $this->errors[] = self::INVALID_NAME;
            return $this;
        }
        $this->name = $name;
        return $this;
    }

    public function setLogo(string $logo): SocialNetwork
    {
        if (empty($logo)) {
            $this->errors[] = self::INVALID_LOGO;
            return $this;
        }
        $this->logo = $logo;
        return $this;
    }

    public function setUrl(string $url)
    {
        if (empty($url) || !preg_match('/^[-&%_:?\/=.\da-z]{5,50}$/i', $url)) {
            $this->errors[] = self::INVALID_URL;
            return $this;
        }
        $this->url = $url;
        return $this;
    }


    // GETTERS //

    public function getBlogId(): int
    {
        return $this->blogId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLogo(): string
    {
        return $this->logo;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}