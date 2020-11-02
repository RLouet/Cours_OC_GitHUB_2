<?php


namespace Blog\Entities;


use Core\Entity;
use Core\ObjectCollection;
use SplObjectStorage;
use DateTime;

class BlogPost extends Entity
{
    protected int $userId;
    protected User $user;

    protected string $title,
        $chapo,
        $content;

    protected ObjectCollection $images;

    protected $editDate;
    protected ?int $heroId = null;
    protected ?PostImage $hero = null;

    const INVALID_USER_ID = 1;
    const INVALID_TITLE = 2;
    const INVALID_CHAPO = 3;
    const INVALID_CONTENT = 4;
    const INVALID_EDIT_DATE = 5;
    const INVALID_HERO = 6;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->images = new ObjectCollection();
    }

    public function isValid()
    {
        return !(empty($this->userId) || empty($this->title) || empty($this->chapo) || empty($this->content) || empty($this->editDate));
    }

    //  Setters  //

    public function setUserId(int $userId): BlogPost
    {
        if (empty($userId)) {
            $this->errors[] = self::INVALID_USER_ID;
            return $this;
        }
        $this->userId = $userId;
        return $this;
    }

    public function setUser(User $user): BlogPost
    {
        $this->user = $user;
        return $this;
    }

    public function setTitle(string $title): BlogPost
    {
        if (empty($title)) {
            $this->errors[] = self::INVALID_TITLE;
            return $this;
        }
        $this->title = $title;
        return $this;
    }

    public function setChapo(string $chapo): BlogPost
    {
        if (empty($chapo)) {
            $this->errors[] = self::INVALID_CHAPO;
            return $this;
        }
        $this->chapo = $chapo;
        return $this;
    }

    public function setContent(string $content): BlogPost
    {
        if (empty($content)) {
            $this->errors[] = self::INVALID_CONTENT;
            return $this;
        }
        $this->content = $content;
        return $this;
    }

    public function setEditDate(DateTime $editDate): BlogPost
    {
        if (empty($editDate)) {
            $this->errors[] = self::INVALID_EDIT_DATE;
            return $this;
        }
        $this->editDate = $editDate;
        return $this;
    }

    public function setHero(PostImage $hero): BlogPost
    {
        if (empty($hero)) {
            $this->errors[] = self::INVALID_HERO;
            return $this;
        }
        $this->hero = $hero;
        return $this;
    }

    public function addImage(PostImage $image)
    {
        if (!$this->images->contains($image)) {
            $this->images->attach($image);
        }
    }
    public function removeImage(PostImage $image)
    {
        if ($this->images->contains($image)) {
            $this->images->detach($image);
        }
    }

    /*public function setHeroId(int $heroId): BlogPost
    {
        if (empty($heroId)) {
            $this->errors[] = self::INVALID_HERO_ID;
            return $this;
        }
        $this->heroId = $heroId;
        return $this;
    }*/





    //  -- Getters --  //

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getChapo(): string
    {
        return $this->chapo;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getEditDate(): DateTime
    {
        if ($this->editDate instanceof DateTime) {
            return $this->editDate;
        }

        return new DateTime($this->editDate);
    }

    public function getHero(): ?PostImage
    {
        return $this->hero;
    }

    public function getHeroId(): ?int
    {
        //return null;
        return $this->heroId;
    }

    public function getImages(): ObjectCollection
    {
        return $this->images;
    }
}