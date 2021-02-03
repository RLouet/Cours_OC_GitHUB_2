<?php


namespace Core;


use Blog\Entities\User;
use Blog\Models\UserManagerPDO;
use \PDO;

class Auth
{
    private static ?Auth $instance = null;
    private HTTPRequest $httpRequest;
    private HTTPResponse $httpResponse;

    private function __construct()
    {
        $this->httpRequest = HTTPRequest::getInstance();
        $this->httpResponse = new HTTPResponse();
    }

    public static function getInstance()
    {
        if(self::$instance === null)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Login the user
     */
    public function login(User $user, $remembeMe): void
    {
        session_regenerate_id(true);
        $this->httpResponse->setSession('user_id', $user->getId());

        if ($remembeMe) {
            $this->rememberLogin($user);
        }
    }

    /**
     * Logout the user
     */
    public function logout(): void
    {
        // Détruit toutes les variables de session
        $_SESSION = array();

        // Efface le cookie de session.
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Détruit la session.
        session_destroy();

        $this->forgetLogin();
    }

    /**
     * Remember the requested page
     */
    public function rememberRequestedPage(): void
    {
        $this->httpResponse->setSession('return_to', $this->httpRequest->requestUri());
    }

    /**
     * Get the requested page
     */
    public function GetRequestedPage(): string
    {
        return $this->httpRequest->sessionData('return_to') ?? '';
    }

    /**
     * Get the current user
     */
    public function getUser(): ?User
    {
        $userManager = new UserManagerPDO(PDOFactory::getPDOConnexion());
       if ($this->httpRequest->sessionData('user_id')) {
           $user = $userManager->findById($this->httpRequest->sessionData('user_id'));
           if ($user && $user->getBanished()) {
               $this->logout();
               return null;
           }
           return $user;
       }
       return $this->loginRemembered($userManager);
    }

    /**
     * Login user if login is remembered
     */
    private function loginRemembered(Manager $userManager): ?User
    {
        $cookie = $this->httpRequest->cookieData('remember_me');
        if ($cookie) {
            $rememberedLogin = $this->findByToken($cookie);
            if ($rememberedLogin && !$this->hasExpired($rememberedLogin)) {
                $user = $userManager->findById($rememberedLogin['user_id']);
                if ($user && !$user->getBanished()) {
                    $this->login($user, false);
                    return $user;
                }
            }
            $this->forgetLogin();
        }
        return null;
    }

    private function rememberLogin(User $user): bool
    {
        $token = new Token();
        $hashedToken = $token->getHash();
        $token = $token->getValue();
        $expiryTimestamp = time() + 60 * 60 * 24 * 183;

        $sql = 'INSERT INTO remembered_login SET token_hash=:token_hash, user_id=:user_id, expires_at=:expires_at';
        $stmt = PDOFactory::getPDOConnexion()->prepare($sql);
        $stmt->bindValue(':token_hash', $hashedToken, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $user->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiryTimestamp), PDO::PARAM_STR);

        if ($stmt->execute()) {
            $this->httpResponse->setCookie('remember_me', $token, $expiryTimestamp, '/');
            return true;
        }
        return false;
    }

    private function forgetLogin()
    {
        $cookie = $this->httpRequest->cookieData('remember_me');
        if ($cookie) {
            $token = new Token($cookie);
            $hashedToken = $token->getHash();
            $sql = 'DELETE FROM remembered_login WHERE token_hash=:token_hash';
            $stmt = PDOFactory::getPDOConnexion()->prepare($sql);
            $stmt->bindValue(':token_hash', $hashedToken, PDO::PARAM_STR);

            $stmt->execute();

            $this->httpResponse->setCookie('remember_me', '', time() - 36000);
        }
    }

    private function findByToken($token)
    {
        $token = new Token($token);
        $hashedToken = $token->getHash();

        $sql = 'SELECT * FROM remembered_login WHERE token_hash =:token_hash';
        $stmt = PDOFactory::getPDOConnexion()->prepare($sql);
        $stmt->bindValue(':token_hash', $hashedToken, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch();
    }

    private function hasExpired(array $token)
    {
        return strtotime($token['expires_at']) < time();
    }
}