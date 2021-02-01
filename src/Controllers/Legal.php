<?php


namespace Blog\Controllers;


use Blog\Entities\ContactMessage;
use Blog\Services\MailService;
use Core\Auth;
use Core\Config;
use Core\Controller;
use Core\Flash;
use Core\HTTPResponse;

class Legal extends Controller
{
    /**
     * Show the cgu page
     *
     * @return void
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function cguAction()
    {
        $this->httpResponse->renderTemplate('Legal/cgu.html.twig', [
            'section' => 'legal'
        ]);
    }

    /**
     * Show the confidentiality policies page
     *
     * @return void
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function confidentialityAction()
    {
        $this->httpResponse->renderTemplate('Legal/confidentiality.html.twig', [
            'section' => 'legal'
        ]);
    }
}