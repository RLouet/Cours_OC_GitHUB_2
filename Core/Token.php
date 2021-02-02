<?php


namespace Core;


class Token
{
    protected string $token;

    public function __construct(string $tokenValue = null)
    {
        $this->token = $tokenValue?$tokenValue:bin2hex(random_bytes(16));
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