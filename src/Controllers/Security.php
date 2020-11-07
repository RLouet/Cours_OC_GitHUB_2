<?php


namespace Blog\Controllers;


use Blog\Entities\User;
use Core\Auth;
use Core\Controller;
use Core\HTTPResponse;

class Security extends Controller
{
    /**
     * Show the registration page
     *
     * @return void
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function registrationAction()
    {
        $manager = $this->managers->getManagerOf('Blog');
        $blog = $manager->getData();

        $flash = [
            'type' => false,
            'messages' => []
        ];

        $user['entity'] = new User();

        if ($this->httpRequest->postExists('register-btn')) {
            if (!$this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $flash['type'] = 'error';
                $flash['messages'][] = 'Erreur lors de la vérification du formulaire.';
            } else {
                $user = $this->processForm();
                if (empty($user['errors'])) {
                    $flash['type'] = 'success';
                    $flash['messages'][] = 'Vous avez bien été enregistrés';
                    HTTPResponse::redirect('');
                } else {
                    $flash['type'] = 'error';
                    $flash['messages'] = $user['errors'];
                }

            }
        }

        $csrf = $this->generateCsrfToken();

        HTTPResponse::renderTemplate('Security/Signup.html.twig', [
            'section' => 'security',
            'blog' => $blog,
            'flash' => $flash,
            'user' => $user,
            'csrf_token' => $csrf
        ]);
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
    public function loginAction()
    {
        $manager = $this->managers->getManagerOf('Blog');
        $blog = $manager->getData();

        $flash = [
            'type' => false,
            'messages' => []
        ];


        if ($this->httpRequest->postExists('login-btn')) {
            if (!$this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $flash['type'] = 'error';
                $flash['messages'][] = 'Erreur lors de la vérification du formulaire.';
            } else {
                $userManager =  $this->managers->getManagerOf('user');
                $user = $userManager->findByEmail($this->httpRequest->postData('email'));

                if ($user) {
                    if (password_verify($this->httpRequest->postData('password'), $user->getPassword())) {
                        Auth::login($user);
                        HTTPResponse::redirect(Auth::GetRequestedPage());
                    }
                }
                $flash['type'] = 'error';
                $flash['messages'][] = 'Mauvaise combinaison email / mot de passe.';
            }
        }

        $csrf = $this->generateCsrfToken();

        HTTPResponse::renderTemplate('Security/login.html.twig', [
            'section' => 'security',
            'blog' => $blog,
            'flash' => $flash,
            'email' => $this->httpRequest->postData('email'),
            'csrf_token' => $csrf
        ]);
    }

    /**
     * Show the index page
     *
     * @return void
     *
     */
    public function logoutAction()
    {
        Auth::logout();
        HTTPResponse::redirect('');
    }

    function processForm()
    {
        $userManager =  $this->managers->getManagerOf('user');

        $user = new User($this->httpRequest->postData());

        if ($this->httpRequest->postData('plain_password') !== $this->httpRequest->postData('confirm_password')) {
        $user->setCustomError('confirm_pass', 'Les mots de passe doivent être identiques');
        }

        if ($userManager->mailExists($user->getEmail())) {
            $user->setCustomError('mail', 'Vous êtes déjà enregistré avec cette adresse Email');
        }

        if ($userManager->userExists($user->getEmail())) {
            $user->setCustomError('username', 'Ce pseudo est déjà utilisé');
        }

        $handle['entity'] = $user;

        if ($user->isValid() && empty($user->getErrors())) {
            $user = $userManager->save($user);
            if ($user) {
                return $handle;
            }
            $handle['errors'][] = "L'enregistrement a échoué.";
            return $handle;
        }
        $handle['errors'][] = "Vos informations sont invalides.";
        //var_dump($user);
        return $handle;
    }
}