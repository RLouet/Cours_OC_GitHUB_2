<?php


namespace Core;


use Blog\Entities\User;
use Blog\Models\UserManagerPDO;

class Auth
{
    /**
     * Login the user
     */
    public static function login(User $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->getId();
    }

    /**
     * Logout th user
     */
    public static function logout(): void
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
    }

    /**
     * Remember the requested page
     */
    public static function rememberRequestedPage(): void
    {
        $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    }

    /**
     * Get the requested page
     */
    public static function GetRequestedPage(): string
    {
       return $_SESSION['return_to'] ?? '';
    }

    /**
     * Get the current user
     */
    public static function getUser(): ?User
    {
        $userManager = new UserManagerPDO(PDOFactory::getPDOConnexion());
       if (isset($_SESSION['user_id'])) {
           return $userManager->findById($_SESSION['user_id']);
       }
       return null;
    }
}