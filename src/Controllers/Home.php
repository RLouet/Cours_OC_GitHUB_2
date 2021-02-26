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
                $messageFlash = ['message' => "Des champs du formulaire sont invalides. Merci de les corriger et de recommencer.", 'type' => Flash::WARNING];
                if (empty($contactMessage->getErrors())) {
                    $mailer = new MailService();
                    if ($mailer->sendContactEmail($contactMessage)) {
                        $this->flash->addMessage('Merci, votre message a bien été envoyé.');
                        $this->httpResponse->redirect('/#');
                    }
                    $messageFlash = ['message' => "Une erreur s'est produite lors de l'envoie de votre message. Merci de rééssayer plus tard.", 'type' => Flash::ERROR];
                }
                $this->flash->addMessage($messageFlash['message'], $messageFlash['type']);
            }
        }

        $csrf = $this->generateCsrfToken();
        $this->httpResponse->renderTemplate('Frontend/index.html.twig', [
            'section' => 'home',
            'contact_message' => $contactMessage,
            'posted_data' => $postedData,
            'csrf_token' => $csrf
        ]);
    }
}