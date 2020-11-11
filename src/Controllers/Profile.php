<?php


namespace Blog\Controllers;


use Blog\Entities\User;
use Blog\Services\MailService;
use Core\Auth;
use Core\Controller;
use Core\Flash;
use Core\HTTPResponse;
use Core\Token;

class Profile extends Controller
{

    /**
     * Before filter
     */
    protected function before(): void
    {
        $this->requiredLogin('user');
    }

    /**
     * Show the profile page
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function showAction()
    {
        HTTPResponse::renderTemplate('Profile/show.html.twig', [
            'section' => 'security',
        ]);
    }

    /**
     * Show the profile page
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function editAction()
    {
        $user['entity'] = Auth::getUser();

        if ($this->httpRequest->postExists('edit-profile-btn')) {
            if ($this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $user = $this->processEditProfileForm($user['entity']);
                if (empty($user['errors'])) {
                    Flash::addMessage("Votre profile a bien été modifié.");
                    HTTPResponse::redirect('/profile/show');

                }
                foreach ($user['errors'] as $error) {
                    Flash::addMessage($error, Flash::WARNING);
                }
            }
        }

        $csrf = $this->generateCsrfToken();
        HTTPResponse::renderTemplate('Profile/edit.html.twig', [
            'section' => 'security',
            'user' => $user,
            'csrf_token' => $csrf
        ]);
    }

    /**
     * process the edit profile form
     */
    private function processEditProfileForm(User $user): array
    {
        $user->hydrate($this->httpRequest->postData());
        $userManager =  $this->managers->getManagerOf('user');

        /*if ($this->httpRequest->postData('new_password') !== $this->httpRequest->postData('confirm_password')) {
            $user->setCustomError('confirm_pass', 'Les mots de passe doivent être identiques');
        }*/

        if ($userManager->mailExists($user->getEmail(), $user->getId())) {
            $user->setCustomError('mail', "Cette adresse email n'est pas disponible.");
        }

        if ($userManager->userExists($user->getUsername(), $user->getId())) {
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
}