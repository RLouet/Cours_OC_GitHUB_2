<?php


namespace Blog\Controllers;


use Core\Config;
use Core\Controller;
use Core\HTTPResponse;

class Home extends Controller
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

         //var_dump($homeData);

        HTTPResponse::renderTemplate('Frontend/index.html.twig', [
            'blog' => $blog,
        ]);
    }
}