<?php


namespace Core;


class Token
{
    protected string $token;

    public function __construct(string $tokenValue = null)
    {
        if ($tokenValue) {
            $this->token = $tokenValue;
        } else {
            $this->token = bin2hex(random_bytes(16));
        }
    }

    public function getValue(): string
    {
        return $this->token;
    }

    public function getHash(): string
    {
        return hash_hmac('sha256', $this->token, Config::getInstance()->get('secret_key'));
    }
}