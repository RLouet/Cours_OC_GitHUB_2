<?php


namespace Core;


use Blog\Entities\User;
use Blog\Models\UserManagerPDO;
use \PDO;

class Auth
{
    private static ?Auth $instance = null;
    private HTTPRequest $httpRequest;
    private array $session;
    protected Managers $managers;

    private function __construct()
    {
        $this->httpRequest = HTTPRequest::getInstance();
        $this->session = &$_SESSION;
        $this->managers = new Managers('PDO', PDOFactory::getPDOConnexion());
    }

    public static function getInstance(): self
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
        $this->session['user_id'] = $user->getId();

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
        $this->session = array();

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
        $this->session['return_to'] = $this->httpRequest->requestUri();
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
        $userManager = $this->managers->getManagerOf('user');
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
     *
     * @param Manager $userManager
     * @return User|null
     */
    private function loginRemembered(Manager $userManager): ?User
    {
        $cookie = $this->httpRequest->cookieData('remember_me');
        if ($cookie) {
            $token = new Token($cookie);

            $rememberedLoginManager = $this->managers->getManagerOf('RememberedLogin');
            $rememberedLogin = $rememberedLoginManager->findByToken($token);

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
        $expiryTimestamp = time() + 60 * 60 * 24 * 183;

        $rememberedLoginManager = $this->managers->getManagerOf('RememberedLogin');

        if ($rememberedLoginManager->save($user, $token, $expiryTimestamp)) {
            $tokenValue = $token->getValue();
            $httpResponse = new HTTPResponse();
            $httpResponse->setCookie('remember_me', $tokenValue, $expiryTimestamp, '/');
            return true;
        };
        return false;
    }

    private function forgetLogin(): void
    {
        $cookie = $this->httpRequest->cookieData('remember_me');
        if ($cookie) {
            $token = new Token($cookie);

            $rememberedLoginManager = $this->managers->getManagerOf('RememberedLogin');

            $rememberedLoginManager->delete($token);

            $httpResponse = new HTTPResponse();
            $httpResponse->setCookie('remember_me', '', time() - 36000);
        }
    }

    private function hasExpired(array $token): bool
    {
        return strtotime($token['expires_at']) < time();
    }
}