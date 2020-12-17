<?php


namespace Blog\Controllers;


use Blog\Entities\BlogPost;
use Blog\Entities\Skill;
use Blog\Entities\SocialNetwork;
use Blog\Entities\User;
use Blog\Services\FilesService;
use Blog\Services\MailService;
use Core\Auth;
use Core\Config;
use Core\Controller;
use Core\Flash;
use Core\HTTPResponse;

class Ajax extends Controller
{

    /**
     * Before filter
     *
     */
    protected function before()
    {
        if ($this->httpRequest->method() !== 'POST' || !$this->httpRequest->isAjax())
        {
            throw new \Exception('not found', 404);
        }
    }

    /**
     * get skills
     */
    public function typedElementsAction()
    {
        $config = Config::getInstance();
        $blogId = $config->get('blog_id') ? $config->get('blog_id') : 1;
        $manager = $this->managers->getManagerOf('skill');
        $skills = $manager->getListByBlog($blogId);

        $elements = [];
        foreach ($skills as $skill) {
            $elements[]=$skill['value'];
        }

        echo json_encode($elements);
    }


    /**
     * Delete social network
     */
    public function deleteSocialNetworkAction()
    {
        $this->requiredLogin('admin');
        $config = Config::getInstance();
        $blogId = $config->get('blog_id') ? $config->get('blog_id') : 1;

        $handle = [
            'success' => true,
            'errors' => [],
        ];

        $manager = $this->managers->getManagerOf('SocialNetwork');

        $oldSocialNetwork =  $manager->getUnique($this->httpRequest->postData('id'));

        if (!$oldSocialNetwork || $oldSocialNetwork->getBlogId() != $blogId) {
            $handle['success'] = false;
            $handle['errors'][] = 'Le réseau social à supprimer est invalide.';
            echo json_encode($handle);
            exit();
        }

        if (!$manager->delete($oldSocialNetwork->getId())) {
            $handle['success'] = false;
            $handle['errors'][] = 'Error lors de la suppression du réseau social de la base de données.';
            echo json_encode($handle);
            exit();
        }

        $uploader = new FilesService();
        $iconRules = [
            'target' => 'icons',
            'folder' => '/' . $blogId
        ];

        if (!$uploader->deleteFile($iconRules, $oldSocialNetwork->getLogo())) {
            $manager->save($oldSocialNetwork);
            $handle['success'] = false;
            $handle['errors'][] = 'Error lors de la suppression du logo du réseau social.';
            echo json_encode($handle);
            exit();
        }

        $handle['deleted'] = $oldSocialNetwork->getId();
        echo json_encode($handle);
    }


    /**
     * Save social network
     */
    public function saveSocialNetworkAction()
    {
        $this->requiredLogin('admin');
        $config = Config::getInstance();
        $blogId = $config->get('blog_id') ? $config->get('blog_id') : 1;

        $logoUploadRules = [
            'target' => 'icons',
            'folder' => '/' . $blogId,
            'old' => $this->httpRequest->postData('old_logo'),
            'maxSize' => 1,
            'type' => 'image',
            'minRes' => [64, 64],
            'maxRes' => [256, 256]
        ];

        $handle = [
            'success' => true,
            'form_errors' => [],
            'errors' => []
        ];

        $socialNetwork = new SocialNetwork([
            'blogId' => $blogId,
            'name' => $this->httpRequest->postData('name'),
            'url' => $this->httpRequest->postData('url')
        ]);

        if ($this->httpRequest->postExists('id')) {
            $socialNetwork->setId($this->httpRequest->postData('id'));
        }

        $handle['form_errors'] = $socialNetwork->getErrors();

        if (!empty($handle['form_errors'])) {
            $handle['success'] = false;
            $handle['errors'][] = 'Erreur dans le formulaire.';
            echo json_encode($handle);
            exit();
        }

        $manager = $this->managers->getManagerOf('SocialNetwork');

        // Vérification qu'aucun autre réseau du blog courant ne porte le même nom
        $double = $manager->doubleExists($socialNetwork);
        if ($double) {
            $handle['errors'][] = "Un autre réseau social porte déjà ce nom.";
            $handle['success'] = false;
            echo json_encode($handle);
            exit();
        }

        if ($this->httpRequest->postExists('id')) {

            // Création du nom de l'icone
            if (!empty($this->httpRequest->filesData('logo')['name'])) {
                $ext = pathinfo($this->httpRequest->filesData('logo')['name'], PATHINFO_EXTENSION);
                $socialNetwork->setLogo($socialNetwork->getName() . '.' . $ext);
            } else {
                $ext = pathinfo($this->httpRequest->postData('old_logo'), PATHINFO_EXTENSION);
                $socialNetwork->setLogo($socialNetwork->getName() . '.' . $ext);
            }

            $oldSocialNetwork =  $manager->getUnique($socialNetwork->getId());

            // Enregistrement du réseau social
             if (!$manager->save($socialNetwork)) {
                 $handle['success'] = false;
                 $handle['errors'][] = "Erreur lors de l'enregistrement.";
                 echo json_encode($handle);
                 exit();
             }

            $uploader = new FilesService();

             // Enregistrement de l'icone si elle a changé
            if (!empty($this->httpRequest->filesData('logo')['name'])) {
                $upload = $uploader->upload($this->httpRequest->filesData('logo'), $logoUploadRules, $socialNetwork->getName());

                if (!$upload['success']) {
                    $manager->save($oldSocialNetwork);
                    $handle['success'] = false;
                    $handle['errors'][] = $upload['errors'];
                    echo json_encode($handle);
                    exit();
                }
            }

            // Renommage de l'icone si le nom du réseau a changé
            if (($oldSocialNetwork->getName() !== $socialNetwork->getName()) && empty($this->httpRequest->filesData('logo')['name'])) {
                $oldPath = $oldSocialNetwork->getLogo();
                $ext = pathinfo($oldPath, PATHINFO_EXTENSION);
                $newPath = $socialNetwork->getName() . '.' . $ext;

                if (!$uploader->rename($logoUploadRules, $oldPath, $newPath)){
                    $manager->save($oldSocialNetwork);
                    $handle['errors'][] = "Impossible de renommer le fichier.";
                    $handle['success'] = false;
                    echo json_encode($handle);
                    exit();
                }
            }
        } else {
            if (empty($this->httpRequest->filesData('logo')['name'])) {
                $handle['errors'][] = "Le logo est manquant.";
                $handle['success'] = false;
                echo json_encode($handle);
                exit();
            }

            $ext = pathinfo($this->httpRequest->filesData('logo')['name'], PATHINFO_EXTENSION);
            $socialNetwork->setLogo($socialNetwork->getName() . '.' . $ext);

            // Enregistrement du réseau social
            $socialNetwork = $manager->save($socialNetwork);
            if (!$socialNetwork) {
                $handle['success'] = false;
                $handle['errors'][] = "Erreur lors de l'enregistrement.";
                echo json_encode($handle);
                exit();
            }

            $uploader = new FilesService();

            // Enregistrement de l'icone
            $upload = $uploader->upload($this->httpRequest->filesData('logo'), $logoUploadRules, $socialNetwork->getName());

            if (!$upload['success']) {
                $manager->delete($socialNetwork->getId());
                $handle['success'] = false;
                $handle['errors'][] = $upload['errors'];
                echo json_encode($handle);
                exit();
            }
        }

        $handle['entity'] = $socialNetwork;

        echo json_encode($handle);
    }


    /**
     * Save skill
     */
    public function saveSkillAction()
    {
        $this->requiredLogin('admin');

        $config = Config::getInstance();
        $blogId = $config->get('blog_id') ? $config->get('blog_id') : 1;

        $handle = [
            'success' => true,
            'form_errors' => [],
            'errors' => []
        ];

        $skill = new Skill([
            'blogId' => $blogId,
            'value' => $this->httpRequest->postData('skill')
        ]);

        if ($this->httpRequest->postExists('id')) {
            $skill->setId($this->httpRequest->postData('id'));
        }

        $handle['form_errors'] = $skill->getErrors();

        if (!empty($handle['form_errors'])) {
            $handle['success'] = false;
            $handle['errors'][] = 'Erreur dans le formulaire.';
            echo json_encode($handle);
            exit();
        }

        $manager = $this->managers->getManagerOf('Skill');

        // Vérification qu'aucun autre skill du blog courant ne porte le même nom
        $double = $manager->doubleExists($skill);
        if ($double) {
            $handle['errors'][] = "Un autre skill porte déjà ce nom.";
            $handle['success'] = false;
            echo json_encode($handle);
            exit();
        }

        // Enregistrement du skill
         if (!$manager->save($skill)) {
             $handle['success'] = false;
             $handle['errors'][] = "Erreur lors de l'enregistrement.";
             echo json_encode($handle);
             exit();
         }

        $handle['entity'] = $skill;

         //var_dump($skill);

        echo json_encode($handle);
    }

    /**
     * Delete skill
     */
    public function deleteSkillAction()
    {

        $this->requiredLogin('admin');
        $config = Config::getInstance();
        $blogId = $config->get('blog_id') ? $config->get('blog_id') : 1;

        $handle = [
            'success' => true,
            'errors' => [],
        ];

        $manager = $this->managers->getManagerOf('Skill');

        $oldSkill =  $manager->getUnique($this->httpRequest->postData('id'));

        if (!$oldSkill || $oldSkill->getBlogId() != $blogId) {
            $handle['success'] = false;
            $handle['errors'][] = 'Le skill à supprimer est invalide.';
            echo json_encode($handle);
            exit();
        }

        if (!$manager->delete($oldSkill->getId())) {
            $handle['success'] = false;
            $handle['errors'][] = 'Error lors de la suppression du skill.';
            echo json_encode($handle);
            exit();
        }

        $handle['deleted'] = $oldSkill->getId();
        echo json_encode($handle);
    }

    /**
     * Delete post
     */
    public function deletePostAction()
    {
        $this->requiredLogin('admin');

        $user = Auth::getUser();

        $handle = [
            'success' => true,
            'errors' => [],
        ];

        $manager = $this->managers->getManagerOf('BlogPost');

        $oldPost =  $manager->getUnique($this->httpRequest->postData('id'));

        //var_dump($oldPost->getUser() != $user, $oldPost->getUser(), $user);

        if (!$oldPost || ($oldPost->getUser()->getId() != $user->getId() && $oldPost->getuser()->isGranted('admin') && !$oldPost->getUser()->getBanished())) {
            $handle['success'] = false;
            $handle['errors'][] = 'Vous ne pouvez pas supprimer ce post.';
            echo json_encode($handle);
            exit();
        }

        $postDelete = $this->postDeleter($oldPost);
        //var_dump($postDelete);
        if ($postDelete !== 'success') {
            $handle['success'] = false;
            $handle['errors'][] = $postDelete;
            echo json_encode($handle);
            exit();
        }

        $handle['deleted'] = $oldPost->getId();
        echo json_encode($handle);
    }

    /**
     * change Password from profile
     */
    public function changePassword()
    {
        $this->requiredLogin('user');

        $user = Auth::getUser();

        $handle = [
            'success' => true,
            'token_error' => false,
            'old_error' => false,
            'new_error' => false,
            'conf_error' => false,
            'user_error' => false,
            'db_error' => false,
        ];
        if (!$this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
            $handle['success'] = false;
            $handle['token_error'] = true;
            echo json_encode($handle);
            exit();
        }
        if (!password_verify($this->httpRequest->postData('old_password'), $user->getPassword())) {
            $handle['success'] = false;
            $handle['old_error'] = true;
        }
        if ($this->httpRequest->postData('new_password') !== $this->httpRequest->postData('conf_password')) {
            $handle['success'] = false;
            $handle['conf_error'] = true;
        }
        $user->setPlainPassword($this->httpRequest->postData('new_password'));
        if (!empty($user->getErrors())) {
            $handle['success'] = false;
            foreach ($user->getErrors() as $key => $error) {
                if ($error === User::INVALID_PASSWORD) {
                    $handle['new_error'] = true;
                } else {
                    $handle['user_error'] = true;
                }
            }
        }
        if ($handle['success']) {
            if ($user->isValid()) {
                $userManager =  $this->managers->getManagerOf('user');
                $user = $userManager->resetPassword($user);
                if ($user) {
                    echo json_encode($handle);
                    exit();
                }
                $handle['db_error'] = true;
            } else {
                $handle['user_error'] = true;
            }
        }
        $handle['success'] = false;
        echo json_encode($handle);
    }

    /**
     * up user
     */
    public function upUserAction()
    {
        $this->switchRole('ROLE_ADMIN');
    }

    /**
     * down user
     */
    public function downUserAction()
    {
        $this->switchRole('ROLE_USER');
    }

    /**
     * Switch user role
     */
    private function switchRole(string $role) {
        $this->requiredLogin('admin');

        $handle = [
            'success' => true,
            'errors' => [],
        ];

        if (!$this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
            $handle['success'] = false;
            $handle['errors'][] = 'Une erreur s\'est produite.';
            echo json_encode($handle);
            exit();
        }

        $userManager = $this->managers->getManagerOf('user');

        $user =  $userManager->findById($this->httpRequest->postData('id'));

        if ($user->getId() == Auth::getUser()->getId()) {
            $handle['success'] = false;
            $handle['errors'][] = 'Vous ne pouvez pas changer votre role.';
            echo json_encode($handle);
            exit();
        }

        if ($handle['success']) {
            $user->setRole($role);
            if ($user->isValid()) {
                $mailer = new MailService();
                if (!$mailer->sendRoleChangeEmail($user, $this->httpRequest->postData('message_field'))) {
                    $handle['success'] = false;
                    $handle['errors'][] = 'Erreur lors de l\'envoi du mail.';
                    echo json_encode($handle);
                    exit();
                }
                if ($userManager->save($user)) {
                    Flash::addMessage('Le role de l\'utilisateur a bien été modifié.', Flash::SUCCESS);
                    echo json_encode($handle);
                    exit();
                }
                $handle['errors'][] = 'Error lors de l\'enregistrement.';
            } else {
                $handle['errors'][] = 'L\'utilisateur est invalide.';
            }
        }

        $handle['success'] = false;
        echo json_encode($handle);
    }

    /**
     * User banish
     */
    public function banishUserAction()
    {
        $this->switchBanished(true);
    }

    /**
     * User unbanish
     */
    public function unbanishUserAction()
    {
        $this->switchBanished(false);
    }

    /**
     * Switch user banished
     * @param bool $banished
     */

    private function switchBanished(bool $banished) {
        $this->requiredLogin('admin');

        $handle = [
            'success' => true,
            'errors' => [],
        ];

        if (!$this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
            $handle['success'] = false;
            $handle['errors'][] = 'Une erreur s\'est produite.';
            echo json_encode($handle);
            exit();
        }

        $userManager = $this->managers->getManagerOf('user');

        $user =  $userManager->findById($this->httpRequest->postData('id'));

        if ($user->getId() == Auth::getUser()->getId()) {
            $handle['success'] = false;
            $handle['errors'][] = 'Vous ne pouvez pas changer votre état.';
            echo json_encode($handle);
            exit();
        }

        if ($handle['success']) {
            $user->setBanished($banished);
            if ($user->isValid()) {
                $mailer = new MailService();

                if (!$mailer->sendStatusChangeEmail($user, $this->httpRequest->postData('message_field'))) {
                    $handle['success'] = false;
                    $handle['errors'][] = 'Erreur lors de l\'envoi du mail.';
                    echo json_encode($handle);
                    exit();
                }

                if ($this->httpRequest->postData('delete_messages')) {
                    $postDelete = $this->postDeleter($user);
                    //var_dump($postDelete);
                    if ($postDelete !== 'success') {
                        $handle['success'] = false;
                        $handle['errors'][] = $postDelete;
                        echo json_encode($handle);
                        exit();
                    }
                }

                if ($userManager->save($user)) {
                    //Flash::addMessage('L\'état de l\'utilisateur a bien été modifié.', Flash::SUCCESS);
                    echo json_encode($handle);
                    exit();
                }
                $handle['errors'][] = 'Error lors de l\'enregistrement.';
            } else {
                $handle['errors'][] = 'L\'utilisateur est invalide.';
            }
        }

        $handle['success'] = false;
        echo json_encode($handle);
    }

    /**
     * User delete
     */
    public function deleteUserAction()
    {
        $this->requiredLogin('admin');

        $handle = [
            'success' => true,
            'errors' => [],
        ];

        if (!$this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
            $handle['success'] = false;
            $handle['errors'][] = 'Une erreur s\'est produite.';
            echo json_encode($handle);
            exit();
        }

        $userManager = $this->managers->getManagerOf('user');

        $user =  $userManager->findById($this->httpRequest->postData('id'));
        //$user =  $userManager->getWithPosts($this->httpRequest->postData('id'));

        if ($user->getId() == Auth::getUser()->getId()) {
            $handle['success'] = false;
            $handle['errors'][] = 'Vous ne pouvez pas vous supprimer.';
            echo json_encode($handle);
            exit();
        }

        if ($handle['success']) {

            $mailer = new MailService();
            if (!$mailer->sendUserDeleteEmail($user, $this->httpRequest->postData('message_field'))) {
                $handle['success'] = false;
                $handle['errors'][] = 'Erreur lors de l\'envoi du mail.';
                echo json_encode($handle);
                exit();
            }

            $deleter = new FilesService();
            if (!$deleter->deleteDirectory('uploads/blog/' . $user->getId())) {
                $handle['success'] = false;
                $handle['errors'][] = "Erreur lors de la suppression des images";
                echo json_encode(handle);
                exit();
            }
            if ($userManager->delete($user->getId())) {
                Flash::addMessage('L\'utilisateur a bien été supprimé.', Flash::SUCCESS);
                echo json_encode($handle);
                exit();
            }
            $handle['errors'][] = 'Error lors de la suppression.';
        }
        $handle['success'] = false;
        echo json_encode($handle);
    }

    private function postDeleter($toDelete)
    {
        $classes = [
            'User' => 'Blog\Entities\User',
            'BlogPost' => 'Blog\Entities\BlogPost'
        ];
        $type = get_class($toDelete);
        //var_dump($type);
        if ($type !== $classes['User'] && $type !== $classes['BlogPost']) {
            return 'Error lors de la suppression.';
        }

        $deleter = new FilesService();
        if ($type === $classes['User']) {
            $dir = "uploads/blog/" . $toDelete->getId();
        }
        if ($type === $classes['BlogPost']) {
            $dir = "uploads/blog/" . $toDelete->getUser()->getId() . '/' . $toDelete->getId();
        }
        if (!$deleter->deleteDirectory($dir)) {
            return 'Error lors de la suppression des images.';
        }

        $manager = $this->managers->getManagerOf('BlogPost');
        if ($type === $classes['User']) {
            if (!$manager->deleteByUser($toDelete->getId())) {
                return 'Error lors de la suppression des posts.';
            }
        }
        if ($type === $classes['BlogPost']) {
            if (!$manager->delete($toDelete->getId())) {
                return 'Error lors de la suppression du post.';
            }
        }
        return 'success';

    }
}