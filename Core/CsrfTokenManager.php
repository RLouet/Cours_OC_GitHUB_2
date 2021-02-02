<?php


namespace Core;


trait CsrfTokenManager
{
    private HTTPRequest $request;
    private HTTPResponse $response;

    public function __construct()
    {
        $this->response = new HTTPResponse();
        $this->request = HTTPRequest::getInstance();
    }

    public function generateCsrfToken(): string
    {
        $token = md5(uniqid(rand(), true));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    public function isCsrfTokenValid(string $token, bool $flash = true): bool
    {
        if ($this->request->sessionData('csrf_token') !== $token) {
            if ($flash) {
                Flash::addMessage('Erreur lors de la vérification du formulaire.', Flash::WARNING);
            }
            return false;
        }

        return true;
    }

}