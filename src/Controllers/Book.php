<?php


namespace Blog\Controllers;


use Core\Config;
use Core\Controller;
use Core\HTTPResponse;

class Book extends Controller
{
    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        //echo '<p>(before)</p>';
        //return false;
    }

    /**
     * After filter
     *
     * @return void
     */
    protected function after()
    {
        //echo '<p>(after)</p>';
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
        /*$config = new Config();
        echo $config->get('show_errors');*/
        $manager = $this->managers->getManagerOf('Blog');
        $blog = $manager->getData();
        $postManager = $this->managers->getManagerOf('BlogPost');
        $posts = $postManager->getList();

         //var_dump($homeData);

        HTTPResponse::renderTemplate('Frontend/blog.html.twig', [
            'section' => 'book',
            'blog' => $blog,
            'posts' => $posts,
        ]);
    }

    public function viewAction()
    {
        //$this->requiredLogin('admin');

        $blogManager = $this->managers->getManagerOf('Blog');
        $blog = $blogManager->getData();

        $flash = [
            'type' => false,
            'messages' => []
        ];

        $postManager = $this->managers->getManagerOf('BlogPost');
        $blogPost['entity'] = $postManager->getUnique($this->route_params['id']);

        HTTPResponse::renderTemplate('Frontend/post-view.html.twig', [
            'section' => 'book',
            'blog' => $blog,
            'blog_post' => $blogPost,
            'flash' => $flash
        ]);
    }
}