<?php


namespace Blog\Entities;


use Core\Entity;
use \DateTime;

class User extends Entity
{
    protected string $username = "";
    protected string $lastname = "";
    protected string $firstname = "";
    protected string $email = "";
    protected string $password;
    protected string $role = "ROLE_USER";
    protected ?string $passwordResetHash = null;
    protected ?DateTime $passwordResetExpiry = null;
    protected ?string $activationHash = null;
    protected bool $enabled = false;
    protected ?string $newEmail = null;
    protected bool $banished = false;

    const INVALID_USERNAME = 1;
    const INVALID_LASTNAME = 2;
    const INVALID_FIRSTNAME = 3;
    const INVALID_EMAIL = 4;
    const INVALID_PASSWORD = 5;
    const INVALID_ROLE = 6;
    const INVALID_NEW_EMAIL = 7;

    public function isValid(): bool
    {
        return !(empty($this->username) || empty($this->lastname) || empty($this->firstname) || empty($this->email) || empty($this->password) || empty($this->role));
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        if (empty($username) || !preg_match('/^.{2,32}$/i', $username)) {
            $this->errors[] = self::INVALID_USERNAME;
            return $this;
        }
        $this->username = $username;
        return $this;
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

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        // Min 8 / Max 50 characters, at least one letter uppercase, one letter lowercase and one number

        if (empty($password) || !preg_match('/^(\$argon2i).*$/', $password)) {
            $this->errors[] = self::INVALID_PASSWORD;
            return $this;
        }

        $this->password = $password;
        return $this;
    }

    public function setPlainPassword(string $password): self
    {
        // Min 8 / Max 50 characters, at least one letter uppercase, one letter lowercase and one number

        if (empty($password) || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,50}$/', $password)) {
            $this->errors[] = self::INVALID_PASSWORD;
            return $this;
        }

        $this->setPassword(password_hash($password, PASSWORD_ARGON2ID));
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        if ($role !=="ROLE_ADMIN" && $role !=="ROLE_USER") {
            $this->errors[] = self::INVALID_ROLE;
            return $this;
        }
        $this->role = $role;
        return $this;
    }

    public function getPasswordResetHash(): ?string
    {
        return $this->passwordResetHash;
    }

    public function setPasswordResetHash(?string $passwordResetHash): self
    {
        $this->passwordResetHash = $passwordResetHash;
        return $this;
    }

    public function getPasswordResetExpiry(): ?DateTime
    {
        return $this->passwordResetExpiry;
    }

    public function setPasswordResetExpiry(?DateTime $passwordResetExpiry): self
    {
        $this->passwordResetExpiry = $passwordResetExpiry;
        return $this;
    }

    public function getActivationHash(): ?string
    {
        return $this->activationHash;
    }

    public function setActivationHash(?string $activationHash): self
    {
        $this->activationHash = $activationHash;
        return $this;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getNewEmail(): ?string
    {
        return $this->newEmail;
    }

    public function setNewEmail(?string $newEmail): self
    {
        if (!isset($newEmail)) {
            return $this;
        }
        if (empty($newEmail) || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = self::INVALID_NEW_EMAIL;
            return $this;
        }
        $this->newEmail = $newEmail;
        return $this;
    }

    public function resetNewEmail(): self
    {
        $this->newEmail = null;
        $this->activationHash = null;
        return $this;
    }

    public function getBanished(): bool
    {
        return $this->banished;
    }

    public function setBanished(bool $banished): self
    {
        $this->banished = $banished;
        return $this;
    }

    public function isGranted($role):bool
    {
        if (isset($this->role)) {
            if ($role === "user") {
                if ($this->role === "ROLE_USER" || $this->role === "ROLE_ADMIN") {
                    return true;
                }
            }
            if ($role === "admin") {
                if ($this->role === "ROLE_ADMIN") {
                    return true;
                }
            }
        }
        return false;
    }

}