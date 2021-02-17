<?php


namespace Blog\Entities;


use Core\Entity;
use \SplObjectStorage;

class Blog extends Entity
{
    protected string $lastname;
    protected string $firstname;
    protected string $email;
    protected string $phone;
    protected string $logo;
    protected string $teaserPhrase;
    protected string $contactMail;
    protected string $cv;
    protected  SplObjectStorage $skills;
    protected  SplObjectStorage $socialNetworks;

    const INVALID_LASTNAME = 1;
    const INVALID_FIRSTNAME = 2;
    const INVALID_EMAIL = 3;
    const INVALID_PHONE = 4;
    const INVALID_LOGO = 5;
    const INVALID_TEASER = 6;
    const INVALID_CONTACTMAIL = 7;
    const INVALID_CV = 8;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->socialNetworks = new SplObjectStorage();
        $this->skills = new SplObjectStorage();
    }

    public function  isValid()
    {
        return !(empty($this->lastname) || empty($this->firstname) || empty($this->logo) || empty($this->teaserPhrase) || empty($this->contactMail) || empty($this->cv) ||empty($this->email) || empty($this->phone) || !empty($this->errors));
    }


    // SETTERS //

    public function setLastname(string $lastname): Blog
    {
        $this->lastname = $lastname;
        if (empty($lastname) || !preg_match('/^[a-zÀ-ÖØ-öø-ÿœŒ\'][a-z-\' À-ÖØ-öø-ÿœŒ]{0,48}[a-zÀ-ÖØ-öø-ÿœŒ\']$/i', $lastname)) {
            $this->errors[] = self::INVALID_LASTNAME;
            return $this;
        }

        return $this;
    }

    public function setFirstname(string $firstname): Blog
    {
        $this->firstname = $firstname;
        if (empty($firstname) || !preg_match('/^[a-zÀ-ÖØ-öø-ÿœŒ\'][À-ÖØ-öø-ÿœŒa-z-\' ]{0,48}[À-ÖØ-öø-ÿœŒa-z\']$/i', $firstname)) {
            $this->errors[] = self::INVALID_FIRSTNAME;
            return $this;
        }
        return $this;
    }

    public function setEmail(string $email): Blog
    {
        $this->email = $email;
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = self::INVALID_EMAIL;
            return $this;
        }
        return $this;
    }

    public function setPhone(string $phone): Blog
    {
        $this->phone = $phone;
        if (empty($phone) || !preg_match('/^[\d+(][\d. ()+-]{6,28}[\d)]$/', $phone)) {
            $this->errors[] = self::INVALID_PHONE;
            return $this;
        }
        return $this;
    }

    public function setLogo(string $logo): Blog
    {
        $this->logo = $logo;
        if (empty($logo)) {
            $this->errors[] = self::INVALID_LOGO;
            return $this;
        }
        return $this;
    }

    public function setTeaserPhrase(string $teaser): Blog
    {
        $this->teaserPhrase = $teaser;
        if (empty($teaser)) {
            $this->errors[] = self::INVALID_TEASER;
            return $this;
        }
        return $this;
    }

    public function setContactMail(string $contactMail): Blog
    {
        $this->contactMail = $contactMail;
        if (empty($contactMail) || !filter_var($contactMail, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = self::INVALID_CONTACTMAIL;
            return $this;
        }
        return $this;
    }

    public function setCv(string $cv): Blog
    {
        $this->cv = $cv;
        if (empty($cv)) {
            $this->errors[] = self::INVALID_CV;
            return $this;
        }
        return $this;
    }

    public function addSocialNetwork(SocialNetwork $socialNetwork): Blog
    {
        if (!$this->socialNetworks->contains($socialNetwork)) {
            $this->socialNetworks->attach($socialNetwork);
        }
        return $this;
    }
    public function removeSocialNetwork(SocialNetwork $socialNetwork): Blog
    {
        if ($this->socialNetworks->contains($socialNetwork)) {
            $this->socialNetworks->detach($socialNetwork);
        }
        return $this;
    }

    public function addSkill(Skill $skill): Blog
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->attach($skill);
        }
        return $this;
    }
    public function removeSkill(Skill $skill): Blog
    {
        if ($this->skills->contains($skill)) {
            $this->skills->detach($skill);
        }
        return $this;
    }


    // GETTERS //

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getfirstname(): string
    {
        return $this->firstname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getLogo(): string
    {
        return $this->logo;
    }

    public function getTeaserPhrase(): string
    {
        return $this->teaserPhrase;
    }

    public function getContactMail(): string
    {
        return $this->contactMail;
    }

    public function getCv(): string
    {
        return $this->cv;
    }

    public function getSocialNetworks(): array
    {
        $socialNetworks = [];

        foreach ($this->socialNetworks as $socialNetwork) {
            $socialNetworks[] = $socialNetwork;
        }

        return $socialNetworks;
    }

    public function getSkills(): array
    {
        $skills = [];

        foreach ($this->skills as $skill) {
            $skills[] = $skill;
        }

        return $skills;
    }
}