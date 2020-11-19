<?php


namespace Core;


trait CsrfTokenManager
{
    public function generateCsrfToken(): string
    {
        $token = md5(uniqid(rand(), true));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    public function isCsrfTokenValid(?string $token): bool
    {
        if (empty($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $token) {
            Flash::addMessage('Erreur lors de la vérification du formulaire.', Flash::WARNING);
            return false;
        }

        return true;
    }

}