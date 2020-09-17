<?php


namespace Blog\Controllers;


use Core\Controller;
use Core\HTTPResponse;

class Ajax extends Controller
{

    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        //var_dump($_POST);
        /*if ($this->httpRequest->method() !== 'POST')
        {
            throw new \Exception('not found', 404);
        }*/
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

        HTTPResponse::renderTemplate('Backend/index.html.twig', [
            'section' => 'accueil',
        ]);
    }

    public function typedElementsAction()
    {
        /*$config = new Config();
        echo $config->get('show_errors');*/

        $elements = ["Développeur PHP", "Développeur Symfony", "Développeur Wordpress", "WebDesigner", "Infographiste 3D", "Maker"];

        //echo "test";
        echo json_encode($elements);
    }
}