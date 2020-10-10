<?php


namespace Blog\Controllers\Admin;


use Core\Controller;
use Core\HTTPResponse;

class Admin extends Controller
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
        $manager = $this->managers->getManagerOf('Blog');
        $blog = $manager->getData();
        /*$config = new Config();
        echo $config->get('show_errors');*/

        HTTPResponse::renderTemplate('Backend/index.html.twig', [
            'section' => 'accueil',
            'blog' => $blog,
        ]);
    }
}