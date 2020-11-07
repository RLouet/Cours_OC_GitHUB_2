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
     * After filter
     */
    protected function after(): void
    {
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
        $manager = $this->managers->getManagerOf('Blog');
        $blog = $manager->getData();
        $postManager = $this->managers->getManagerOf('BlogPost');
        $posts = $postManager->getList();
        /*$config = new Config();
        echo $config->get('show_errors');*/

        HTTPResponse::renderTemplate('Backend/index.html.twig', [
            'section' => 'accueil',
            'blog' => $blog,
            'posts' => $posts,
        ]);
    }
}