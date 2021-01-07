<?php


namespace Blog\Controllers\Admin;


use Core\Controller;
use Core\HTTPResponse;

class Admin extends Controller
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
        $postManager = $this->managers->getManagerOf('BlogPost');
        $posts = $postManager->getList();
        $commentManager = $this->managers->getManagerOf('Comment');
        $comments = $commentManager->getUnvalidated();

        $this->httpResponse->renderTemplate('Backend/index.html.twig', [
            'section' => 'accueil',
            'posts' => $posts,
            'comments' => $comments,
        ]);
    }
}