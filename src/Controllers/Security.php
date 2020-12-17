<?php


namespace Blog\Controllers;


use Blog\Entities\User;
use Blog\Services\MailService;
use Core\Auth;
use Core\Controller;
use Core\Flash;
use Core\HTTPResponse;
use Core\Token;
use \DateTime;
use \DateInterval;

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
        $user['entity'] = new User();

        if ($this->httpRequest->postExists('register-btn')) {
            if ($this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $user = $this->processRegistrationForm();
                if (empty($user['errors'])) {
                        Flash::addMessage("Vous avez bien été enregistré. Un email de confimation vous a été envoyé afin d'activer votre compte.");
                        $this->httpResponse->redirect('/login');

                }
                foreach ($user['errors'] as $error) {
                    Flash::addMessage($error, Flash::WARNING);
                }
            }
        }

        $csrf = $this->generateCsrfToken();

        $this->httpResponse->renderTemplate('Security/Signup.html.twig', [
            'section' => 'security',
            'user' => $user,
            'csrf_token' => $csrf
        ]);
    }

    /**
     * Reset password
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function activateAccountAction(): void
    {
        $token = $this->route_params['token'];

        $userManager =  $this->managers->getManagerOf('user');

        $userManager->activate($token);

        Flash::addMessage('Votre compte a bien été activé. Vous pouvez vous connecter');
        $this->httpResponse->redirect('/login');
    }

    /**
     * change Email
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function changeEmailAction(): void
    {
        $token = $this->route_params['token'];

        $userManager =  $this->managers->getManagerOf('user');

        $userManager->changeEmail($token);

        Flash::addMessage('Votre nouvelle adresse Email est validée. Vous pouvez vous reconnecter');
        $this->httpResponse->redirect('/login');
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
        $rememberMe = $this->httpRequest->postExists('remember_me');
        if ($this->httpRequest->postExists('login-btn')) {
            if ($this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $userManager =  $this->managers->getManagerOf('user');
                $user = $userManager->findByEmail($this->httpRequest->postData('email'));

                if ($user && $user->getEnabled()) {
                    if ($user->getBanished()) {
                        Flash::addMessage('Vous avez été bani de ce site !', Flash::ERROR);

                        $this->httpResponse->redirect('');
                    }
                    if (password_verify($this->httpRequest->postData('password'), $user->getPassword())) {
                        Auth::login($user, $rememberMe);

                        $this->httpResponse->redirect(Auth::GetRequestedPage());
                    }
                }
                Flash::addMessage('Mauvaise combinaison email / mot de passe ou compte non activé.', Flash::WARNING);
            }
        }

        $csrf = $this->generateCsrfToken();

        $this->httpResponse->renderTemplate('Security/login.html.twig', [
            'section' => 'security',
            'email' => $this->httpRequest->postData('email'),
            'remember_me' => $rememberMe,
            'csrf_token' => $csrf
        ]);
    }

    /**
     * Forgot password
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function forgotPasswordAction(): void
    {
        if ($this->httpRequest->postExists('forgot-btn')) {
            if ($this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $userManager =  $this->managers->getManagerOf('user');
                $user = $userManager->findByEmail($this->httpRequest->postData('email'));

                if ($user) {
                    $token = new Token();
                    $hashedToken = $token->getHash();
                    $expiryDate = new DateTime();
                    $expiryDate->add(new DateInterval('PT2H'));
                    $user->setPasswordResetHash($hashedToken);
                    $user->setPasswordResetExpiry($expiryDate);
                    if ($userManager->startPasswordReset($user)) {
                        $mailer = new MailService();
                        if ($mailer->sendPasswordResetEmail($user, $token->getValue())) {
                            Flash::addMessage("Un email de récupération vous a été envoyé à l'adresse " . $this->httpRequest->postData('email'));
                            HTTPResponse::redirect('/login');
                        }
                    }
                    Flash::addMessage("Une erreur s'est produite lors de l'envoie de l'Email de récupération. Merci de rééssayer.", Flash::WARNING);
                } else {
                    Flash::addMessage("Un email de récupération vous a été envoyé à l'adresse " . $this->httpRequest->postData('email'));
                    $this->httpResponse->redirect('/login');
                }
            }
        }

        $csrf = $this->generateCsrfToken();

        $this->httpResponse->renderTemplate('Security/forgot-password.html.twig', [
            'section' => 'security',
            'csrf_token' => $csrf
        ]);
    }

    /**
     * Reset password
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function resetPasswordAction(): void
    {
        $formUser = [];

        $token = $this->route_params['token'];

        $userManager =  $this->managers->getManagerOf('user');
        $user = $userManager->findByPasswordToken($token);

        if (!$user || $user->getPasswordResetExpiry() < new DateTime()) {
            Flash::addMessage("Votre lien de réinitialisation est invalide . Merci de renouveler votre demande.", Flash::WARNING);
            $this->httpResponse->redirect('/security/forgot-password');
        }

        if ($this->httpRequest->postExists('reset-btn')) {
            if ($this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $formUser = $this->processResetPasswordForm($user);

                if (empty($formUser['errors'])) {
                    Flash::addMessage('Votre mot de passe a bien été modifié.');
                    $this->httpResponse->redirect('/login');
                }
                foreach ($formUser['errors'] as $error) {
                    Flash::addMessage($error, Flash::WARNING);
                }
            }
        }

        $csrf = $this->generateCsrfToken();

        $this->httpResponse->renderTemplate('Security/reset-password.html.twig', [
            'section' => 'security',
            'user' => $formUser,
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

        $this->httpResponse->redirect('/security/show-logout-message');
    }

    /**
     * Show a message when user log out.
     * Necessary to add a flash message because the session is destroyed at the end of the logout method.
     */
    public function showLogoutMessageAction()
    {
        Flash::addMessage('Vous êtes déconnectés. A bientôt !');

        $this->httpResponse->redirect('');
    }


    /**
     * process the registration form
     */
    private function processRegistrationForm(): array
    {
        $userManager =  $this->managers->getManagerOf('user');

        $user = new User($this->httpRequest->postData());

        if ($this->httpRequest->postData('plain_password') !== $this->httpRequest->postData('confirm_password')) {
        $user->setCustomError('confirm_pass', 'Les mots de passe doivent être identiques');
        }

        if ($userManager->mailExists($user->getEmail())) {
            $user->setCustomError('mail', 'Vous êtes déjà enregistré avec cette adresse Email');
        }

        if ($userManager->userExists($user->getUsername())) {
            $user->setCustomError('username', 'Ce pseudo est déjà utilisé');
        }

        $handle['entity'] = $user;

        if ($user->isValid() && empty($user->getErrors())) {
            $token = new Token();
            $user->setActivationHash($token->getHash());
            $user = $userManager->save($user);
            if ($user) {
                $mailer = new MailService();
                if ($mailer->sendAccountActivationEmail($user, $token->getValue())) {
                    return $handle;
                }
                $userManager->delete($user->getId());
                $handle['errors'][] = "L'Email de confirmation n'a pas put être envoyé. Merci de rééssayer.";
                return $handle;

            }
            $handle['errors'][] = "L'enregistrement a échoué.";
            return $handle;
        }
        $handle['errors'][] = "Vos informations sont invalides.";
        return $handle;
    }


    /**
     * process the reset password form
     */
    private function processResetPasswordForm(User $user): array
    {
        $user->setPlainPassword($this->httpRequest->postData('plain_password'));

        if ($user->getEmail() !== $this->httpRequest->postData('confirm_email')) {
            $user->setCustomError('confirm_email', "Cette adresse Email n'est pas associée à ce lien.");
        }

        if ($this->httpRequest->postData('plain_password') !== $this->httpRequest->postData('confirm_password')) {
        $user->setCustomError('confirm_pass', 'Les mots de passe doivent être identiques');
        }

        $handle['entity'] = $user;
        $handle['email'] = $this->httpRequest->postData('confirm_email');

        if ($user->isValid() && empty($user->getErrors())) {
            $userManager =  $this->managers->getManagerOf('user');
            $user = $userManager->resetPassword($user);
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