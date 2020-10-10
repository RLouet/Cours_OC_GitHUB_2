<?php


namespace Blog\Entities;


use Core\Entity;
use \SplObjectStorage;

class Blog extends Entity
{
    protected $lastname,
        $firstname,
        $email,
        $phone,
        $logo,
        $teaserPhrase,
        $contactMail,
        $cv,
        $socialNetworks;

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
    }

    public function  isValid()
    {
        return !(empty($this->lastname) || empty($this->firstname) || empty($this->logo) || empty($this->teaserPhrase) || empty($this->contactMail) || empty($this->cv) ||empty($this->email) || empty($this->phone));
    }


    // SETTERS //

    public function setLastname(string $lastname)
    {
        if (empty($lastname) || !preg_match('/^[a-z\'][a-z-\' ]{0,48}[a-z\']$/i', $lastname)) {
            $this->errors[] = self::INVALID_LASTNAME;
            return $this;
        }
        $this->lastname = $lastname;

        return $this;
    }

    public function setFirstname(string $firstname)
    {
        if (empty($firstname) || !preg_match('/^[a-z\'][a-z-\' ]{0,48}[a-z\']$/i', $firstname)) {
            $this->errors[] = self::INVALID_FIRSTNAME;
        } else {
            $this->firstname = $firstname;
        }
    }

    public function setEmail(string $email)
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = self::INVALID_EMAIL;
        } else {
            $this->email = $email;
        }
    }

    public function setPhone(string $phone)
    {
        if (empty($phone) || !preg_match('/^[\d+(][\d. ()+-]{6,28}[\d)]$/', $phone)) {
            $this->errors[] = self::INVALID_PHONE;
        } else {
            $this->phone = $phone;
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

    public function setContactMail(string $contactMail)
    {
        if (empty($contactMail) || !filter_var($contactMail, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = self::INVALID_CONTACTMAIL;
        } else {
            $this->contactMail = $contactMail;
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

    public function email()
    {
        return $this->email;
    }

    public function phone()
    {
        return $this->phone;
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