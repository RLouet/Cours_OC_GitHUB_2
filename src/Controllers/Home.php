<?php


namespace Blog\Controllers;


use Core\Auth;
use Core\Config;
use Core\Controller;
use Core\HTTPResponse;

class Home extends Controller
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
        HTTPResponse::renderTemplate('Frontend/index.html.twig', [
            'section' => 'home',
        ]);
    }
}