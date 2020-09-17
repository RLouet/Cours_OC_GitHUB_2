<?php


namespace Blog\Controllers\Admin;


use Core\Controller;
use Core\HTTPResponse;

class Posts extends Controller
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
    public function index()
    {
        /*$config = new Config();
        echo $config->get('show_errors');*/

        HTTPResponse::renderTemplate('Backend/posts-index.html.twig', [
            'section' => 'posts',
        ]);
    }

}