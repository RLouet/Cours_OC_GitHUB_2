<?php


namespace Blog\Entities;


use Core\Entity;

class ContactMessage extends Entity
{
    protected string $firstname = "";
    protected string $lastname = "";
    protected string $email = "";
    protected ?string $phone = null;
    protected ?string $subject = null;
    protected string $message = "";

    const INVALID_FIRSTNAME = 1;
    const INVALID_LASTNAME = 2;
    const INVALID_EMAIL = 3;
    const INVALID_PHONE = 4;
    const INVALID_SUBJECT = 5;
    const INVALID_MESSAGE = 6;

    public function isValid(): bool
    {
        return !(empty($this->lastname) || empty($this->firstname) || empty($this->email) || empty($this->message));
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        if (empty($lastname) || !preg_match('/^[a-zÀ-ÖØ-öø-ÿœŒ\'][\'a-zÀ-ÖØ-öø-ÿœŒ -]{0,48}[\'a-zÀ-ÖØ-öø-ÿœŒ]$/i', $lastname)) {
            $this->errors[] = self::INVALID_LASTNAME;
            return $this;
        }
        $this->lastname = $lastname;
        return $this;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        if (empty($firstname) || !preg_match('/^[a-zÀ-ÖØ-öø-ÿœŒ\'][\'a-zÀ-ÖØ-öø-ÿœŒ -]{0,48}[\'a-zÀ-ÖØ-öø-ÿœŒ]$/i', $firstname)) {
            $this->errors[] = self::INVALID_FIRSTNAME;
            return $this;
        }
        $this->firstname = $firstname;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = self::INVALID_EMAIL;
            return $this;
        }
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        if (!empty($phone) && !preg_match('/^[\d+. -]{6,30}$/', $phone)) {
            $this->errors[] = self::INVALID_PHONE;
            return $this;
        }
        $this->phone = $phone;
        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        if (!empty($subject) && !preg_match('/^.{3,30}$/', $subject)) {
            $this->errors[] = self::INVALID_SUBJECT;
            return $this;
        }
        $this->subject = $subject;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        if (!preg_match('/^.{15,1000}$/s', $message)) {
            $this->errors[] = self::INVALID_MESSAGE;
            return $this;
        }
        $this->message = $message;
        return $this;
    }
}