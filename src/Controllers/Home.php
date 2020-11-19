<?php


namespace Blog\Controllers;


use Blog\Entities\ContactMessage;
use Blog\Services\MailService;
use Core\Auth;
use Core\Config;
use Core\Controller;
use Core\Flash;
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
        $contactMessage = new ContactMessage();

        $postedData = null;

        if ($this->httpRequest->postExists('contact-send')) {
            $postedData = $this->httpRequest->postData();
            $contactMessage->hydrate($this->httpRequest->postData());
            if ($this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                if (empty($contactMessage->getErrors())) {
                    $mailer = new MailService();
                    if ($mailer->sendContactEmail($contactMessage)) {
                        Flash::addMessage('Merci, votre message a bien été envoyé.');
                        HTTPResponse::redirect('/#');
                    }
                }
                Flash::addMessage('Des champs du formulaire sont invalides. Merci de les corriger et de recommencer.', Flash::WARNING);
            }
        }
        $csrf = $this->generateCsrfToken();
        HTTPResponse::renderTemplate('Frontend/index.html.twig', [
            'section' => 'home',
            'contact_message' => $contactMessage,
            'posted_data' => $postedData,
            'csrf_token' => $csrf
        ]);
    }
}