<?php


namespace Blog\Controllers;


use Blog\Entities\User;
use Core\Auth;
use Core\Controller;
use Core\Flash;
use Core\HTTPResponse;
use Core\Token;

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

        $user['entity'] = new User();

        if ($this->httpRequest->postExists('register-btn')) {
            if ($this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $user = $this->processForm();
                if (empty($user['errors'])) {
                    Flash::addMessage('Vous avez bien été enregistré.');
                    HTTPResponse::redirect('');
                }
                foreach ($user['errors'] as $error) {
                    Flash::addMessage($error, Flash::WARNING);
                }
            }
        }

        $csrf = $this->generateCsrfToken();

        HTTPResponse::renderTemplate('Security/Signup.html.twig', [
            'section' => 'security',
            'blog' => $blog,
            'user' => $user,
            'csrf_token' => $csrf
        ]);
    }

    /**
     * Log in the user
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

        $rememberMe = $this->httpRequest->postExists('remember_me');
        if ($this->httpRequest->postExists('login-btn')) {
            if ($this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $userManager =  $this->managers->getManagerOf('user');
                $user = $userManager->findByEmail($this->httpRequest->postData('email'));

                if ($user) {
                    if (password_verify($this->httpRequest->postData('password'), $user->getPassword())) {
                        Auth::login($user, $rememberMe);

                        Flash::addMessage('Vous êtes connectés en tant que ' . $user->getUsername());
                        HTTPResponse::redirect(Auth::GetRequestedPage());
                    }
                }
                Flash::addMessage('Mauvaise combinaison email / mot de passe.', Flash::WARNING);
            }
        }

        $csrf = $this->generateCsrfToken();

        HTTPResponse::renderTemplate('Security/login.html.twig', [
            'section' => 'security',
            'blog' => $blog,
            'email' => $this->httpRequest->postData('email'),
            'remember_me' => $rememberMe,
            'csrf_token' => $csrf
        ]);
    }

    /**
     * log out the user
     *
     * @return void
     *
     */
    public function logoutAction()
    {
        Auth::logout();

        HTTPResponse::redirect('/security/show-logout-message');
    }

    /**
     * Show a message when user log out.
     * Necessary to add a flash message because the session is destroyed at the end of the logout method.
     */
    public function showLogoutMessageAction()
    {
        Flash::addMessage('Vous êtes déconnectés. A bientôt !');

        HTTPResponse::redirect('');
    }


    /**
     * process the registration form
     */
    function processForm(): array
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
        return $handle;
    }
}