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
     * Show the index page
     *
     * @return void
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function indexAction()
    {
        $userManager = $this->managers->getManagerOf('User');
        $users = $userManager->getList();

        //var_dump($posts);

        $this->httpResponse->renderTemplate('Backend/users-index.html.twig', [
            'section' => 'users',
            'users' => $users,
        ]);
    }
}