<?php


namespace Core;


use Blog\Entities\User;

class Auth
{
    /**
     * Login the user
     */
    public static function login(User $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'role' => $user->getRole()
        ];
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
     * Check user roles
     */
    public static function userRole($role): bool
    {
        if (isset($_SESSION['user']['role'])) {
            if ($role === "user") {
                if ($_SESSION['user']['role'] === "ROLE_USER" || $_SESSION['user']['role'] === "ROLE_ADMIN") {
                    return true;
                }
            }
            if ($role === "admin") {
                if ($_SESSION['user']['role'] === "ROLE_ADMIN") {
                    return true;
                }
            }
        }
        return false;
    }
}