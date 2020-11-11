<?php


namespace Blog\Controllers;


use Core\Config;
use Core\Controller;
use Core\HTTPResponse;

class Book extends Controller
{
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

         //var_dump($homeData);

        HTTPResponse::renderTemplate('Frontend/blog.html.twig', [
            'section' => 'book',
            'posts' => $posts,
        ]);
    }

    public function viewAction()
    {
        $flash = [
            'type' => false,
            'messages' => []
        ];

        $postManager = $this->managers->getManagerOf('BlogPost');
        $blogPost['entity'] = $postManager->getUnique($this->route_params['id']);

        HTTPResponse::renderTemplate('Frontend/post-view.html.twig', [
            'section' => 'book',
            'blog_post' => $blogPost,
            'flash' => $flash
        ]);
    }
}