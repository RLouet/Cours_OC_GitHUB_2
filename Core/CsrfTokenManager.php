<?php


namespace Core;


trait CsrfTokenManager
{
    public function generateCsrfToken(): string
    {
        $token = md5(uniqid(rand(), true));
        $this->httpResponse->setSession('csrf_token', $token);
        return $token;
    }

    public function isCsrfTokenValid(string $token, bool $flash = true): bool
    {
        if ($this->httpRequest->sessionData('csrf_token') !== $token) {
            if ($flash) {
                $this->flash->addMessage('Erreur lors de la vérification du formulaire.', Flash::WARNING);
            }
            return false;
        }

        return true;
    }

}