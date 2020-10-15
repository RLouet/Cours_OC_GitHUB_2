<?php


namespace Core;


trait CsrfTokenManager
{
    public function generateCsrfToken()
    {
        $token = md5(uniqid(rand(), true));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    public function isCsrfTokenValid($token)
    {
        if (empty($_SESSION['csrf_token'])) {
            return false;
        }

        return $_SESSION['csrf_token'] === $token;
    }

}