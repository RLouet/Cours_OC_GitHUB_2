<?php


namespace Blog\Entities;


use Core\Entity;
use \SplObjectStorage;

class Blog extends Entity
{
    protected $lastname,
        $firstname,
        $logo,
        $teaserPhrase,
        $contactMail,
        $cv,
        $socialNetworks;

    const INVALID_LASTNAME = 1;
    const INVALID_FIRSTNAME = 2;
    const INVALID_LOGO = 3;
    const INVALID_TEASER = 4;
    const INVALID_MAIL = 5;
    const INVALID_CV = 6;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->socialNetworks = new SplObjectStorage();
    }

    public function  isValid()
    {
        return !(empty($this->lastname) || empty($this->firstname) || empty($this->logo) || empty($this->teaserPhrase) || empty($this->contactMail) || empty($this->cv));
    }


    // SETTERS //

    public function setLastname(string $lastname)
    {
        if (empty($lastname) || !preg_match('/^[a-z\'][a-z-\' ]{0,48}[a-z\']$/i', $lastname)) {
            $this->errors[] = self::INVALID_LASTNAME;
        } else {
            $this->lastname = $lastname;
        }
    }

    public function setFirstname(string $firstname)
    {
        if (empty($firstname) || !preg_match('/^[a-z\'][a-z-\' ]{0,48}[a-z\']$/i', $firstname)) {
            $this->errors[] = self::INVALID_FIRSTNAME;
        } else {
            $this->firstname = $firstname;
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

    public function setTeaserPhrase(string $teaser)
    {
        if (empty($teaser)) {
            $this->errors[] = self::INVALID_TEASER;
        } else {
            $this->teaserPhrase = $teaser;
        }
    }

    public function setContactMail(string $mail)
    {
        if (empty($mail) || !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = self::INVALID_MAIL;
        } else {
            $this->contactMail = $mail;
        }
    }

    public function setCv(string $cv)
    {
        if (empty($cv)) {
            $this->errors[] = self::INVALID_CV;
        } else {
            $this->cv = $cv;
        }
    }

    public function addSocialNetwork(SocialNetwork $socialNetwork)
    {
        if (!$this->socialNetworks->contains($socialNetwork)) {
            $this->socialNetworks->attach($socialNetwork);
        }
    }
    public function removeSocialNetwork(SocialNetwork $socialNetwork)
    {
        if ($this->socialNetworks->contains($socialNetwork)) {
            $this->socialNetworks->detach($socialNetwork);
        }
    }


    // GETTERS //

    public function lastname()
    {
        return $this->lastname;
    }

    public function firstname()
    {
        return $this->firstname;
    }

    public function logo()
    {
        return $this->logo;
    }

    public function teaserPhrase()
    {
        return $this->teaserPhrase;
    }

    public function contactMail()
    {
        return $this->contactMail;
    }

    public function cv()
    {
        return $this->cv;
    }

    public function socialNetworks() : array
    {
        $socialNetworks = [];

        foreach ($this->socialNetworks as $socialNetwork) {
            $socialNetworks[] = $socialNetwork;
        }

        return $socialNetworks;
    }
}