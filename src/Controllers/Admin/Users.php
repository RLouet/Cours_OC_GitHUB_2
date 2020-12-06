<?php


namespace Blog\Controllers\Admin;


use Core\Controller;

class Users extends Controller
{
    /**
     * Before filter
     */
    protected function before(): void
    {
        $this->requiredLogin('admin');
    }

    /**
     * Show the users index page
     *
     * @param string|null $role
     *
     * @return void
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function indexAction(?string $role = null)
    {
        $userManager = $this->managers->getManagerOf('User');

        $usersCount = $userManager->count();
        $users = $userManager->getList($role);

        $this->httpResponse->renderTemplate('Backend/users-index.html.twig', [
            'section' => 'users',
            'users' => $users,
            'role' => $role,
            'users_count' => $usersCount
        ]);
    }

    /**
     * Show the registered users index page
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function usersAction()
    {
        $this->indexAction("ROLE_USER");
    }

    /**
     * Show the  admins users index page
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function adminsAction()
    {
        $this->indexAction("ROLE_ADMIN");
    }
}