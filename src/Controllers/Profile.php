<?php


namespace Blog\Controllers;


use Blog\Entities\User;
use Blog\Services\FilesService;
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

        $csrf = $this->generateCsrfToken();
        $this->httpResponse->renderTemplate('Profile/show.html.twig', [
            'section' => 'security',
            'csrf_token' => $csrf,
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
        $user['entity'] = Auth::getUser()->resetNewEmail();

        if ($this->httpRequest->postExists('edit-profile-btn')) {
            if ($this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $user = $this->processEditProfileForm($user['entity']);
                if (empty($user['errors'])) {
                    Flash::addMessage("Votre profile a bien été modifié.");
                    if ($user['new_email']) {
                        Flash::addMessage("Pour confirmer le changement de votre adresse Email, merci de suivre les instructions envoyées à l'adresse " . $user['entity']->getNewEmail(), Flash::WARNING);
                    }
                    $this->httpResponse->redirect('/profile/show');

                }
                foreach ($user['errors'] as $error) {
                    Flash::addMessage($error, Flash::WARNING);
                }
            }
        }

        $csrf = $this->generateCsrfToken();
        $this->httpResponse->renderTemplate('Profile/edit.html.twig', [
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
        $user->resetNewEmail();
        $user->hydrate([
            'username' => $this->httpRequest->postData('username'),
            'firstname' => $this->httpRequest->postData('firstname'),
            'lastname' => $this->httpRequest->postData('lastname'),
        ]);
        $userManager =  $this->managers->getManagerOf('user');

        if ($this->httpRequest->postData('new_email') !== $user->getEmail()) {
            $user->setNewEmail($this->httpRequest->postData('new_email'));
            if ($userManager->mailExists($user->getNewEmail(), $user->getId())) {
                $user->setCustomError('mail', "Cette adresse email n'est pas disponible.");
            }
        }

        if ($userManager->userExists($user->getUsername(), $user->getId())) {
            $user->setCustomError('username', 'Ce pseudo est déjà utilisé');
        }

        $handle['entity'] = $user;

        if ($user->isValid() && empty($user->getErrors())) {
            if ($user->getNewEmail()) {
                $token = new Token();
                $user->setActivationHash($token->getHash());
                $mailer = new MailService();
                if (!$mailer->sendMailChangeEmail($user, $token->getValue())) {
                    $handle['errors'][] = "Une erreur s'est produite. Merci de rééssayer ultérieurement";
                    return $handle;
                }
                $handle['new_email'] = true;
            }
            $user = $userManager->save($user);
            if ($user) {
                $handle['entity'] = $user;
                return $handle;
            }
            $handle['errors'][] = "L'enregistrement a échoué.";
            return $handle;
        }
        $handle['errors'][] = "Vos informations sont invalides.";
        return $handle;
    }

    /**
     * Delete profile
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function deleteProfileAction()
    {
        if (!$this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
            $this->httpResponse->redirect('/profile/show');
        }

        $user = Auth::getUser();

        $mailer = new MailService();
        if (!$mailer->sendUserDeleteEmail($user, '')) {
            Flash::addMessage('Une erreur s\'est produite lors de l\'envoie du mail de confirmation. Merci de rééssayer.', Flash::ERROR);
            $this->httpResponse->redirect('/profile/show');
        }

        $deleter = new FilesService();
        if (!$deleter->deleteDirectory('uploads/blog/' . $user->getId())) {
            Flash::addMessage('Une erreur s\'est produite lors de la suppression de vos images. Merci de rééssayer.', Flash::ERROR);
            $this->httpResponse->redirect('/profile/show');
        }


        $userManager = $this->managers->getManagerOf('user');
        if ($userManager->delete($user->getId())) {
            Auth::logout();
            $this->httpResponse->redirect('/security/showDeletedMessage');
        }


        Flash::addMessage('Une erreur s\'est produite lors de la suppression de votre profile. Merci de rééssayer.', Flash::ERROR);
        $this->httpResponse->redirect('/profile/show');
    }

    /**
     * Show a message when user delete hir profile.
     * Necessary to add a flash message because the session is destroyed at the end of the logout method.
     */
    public function showDeletedMessageAction()
    {
        Flash::addMessage('Votre profile a bien été supprime.', 'danger');

        $this->httpResponse->redirect('');
    }
}