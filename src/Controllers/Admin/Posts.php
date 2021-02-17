<?php


namespace Blog\Controllers\Admin;


use Blog\Entities\BlogPost;
use Blog\Entities\Comment;
use Blog\Entities\PostImage;
use Blog\Services\FilesService;
use Core\Auth;
use Core\Controller;
use Core\Flash;
use Core\HTTPResponse;

class Posts extends Controller
{

    /**
     * Before filter
     */
    protected function before(): void
    {
        $this->requiredLogin('admin');
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
    public function indexAction()
    {
        $postManager = $this->managers->getManagerOf('BlogPost');
        $posts = $postManager->getList();

        //var_dump($posts);

        $this->httpResponse->renderTemplate('Backend/posts-index.html.twig', [
            'section' => 'posts',
            'posts' => $posts,
        ]);
    }

    public function viewAction()
    {
        $postManager = $this->managers->getManagerOf('BlogPost');
        $blogPost['entity'] = $postManager->getUnique($this->route_params['id']);
        $commentManager = $this->managers->getManagerOf('comment');

        $currentComment = "";

        if ($this->httpRequest->postExists('comment-send') && $this->auth->getUser()->isGranted('user')) {
            $currentComment = $this->httpRequest->postData('content');
            $comment = new Comment($this->httpRequest->postData());
            $comment->setUser($this->auth->getUser())->setBlogPost($blogPost['entity'])->setValidated($this->auth->getUser()->isGranted('admin'));
            if ($this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                if ($comment->isValid()) {
                    if (!$commentManager->save($comment)) {
                        $this->flash->addMessage('Erreur lors de l\'enregistrement de votre commentaire.', Flash::ERROR);
                    } else {
                        $message = 'Votre commentaire est enregistré.';
                        if (!$this->auth->getUser()->isGranted('admin')) {
                            $message .= ' Il apparaîtra bientôt, après sa validation.';
                        }
                        $this->flash->addMessage( $message, Flash::SUCCESS);
                        $currentComment = "";
                    }
                    //$currentComment = "";
                } else {
                    $this->flash->addMessage('Votre commentaire est invalide.', Flash::WARNING);
                }
            }
        }

        $comments = $commentManager->getByPost($this->auth->getUser(), $blogPost['entity']->getId());

        $csrf = $this->generateCsrfToken();

        $this->httpResponse->renderTemplate('Backend/posts-view.html.twig', [
            'section' => 'posts',
            'blog_post' => $blogPost,
            'comments' => $comments,
            'current_comment' => $currentComment,
            'csrf_token' => $csrf
        ]);
    }

    public function newAction()
    {
        $blogPost['entity'] = new BlogPost(['user' => $this->auth->getUser()]);

        if ($this->httpRequest->postExists('post-add')) {
            if ($this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $blogPost = $this->processForm($blogPost['entity']);
                if ($blogPost['saved']) {
                    $this->flash->addMessage('Le post a bien été enregistré.');
                }
                if (empty($blogPost['errors'])) {
                    $this->httpResponse->redirect('/admin/posts');
                }
                foreach ($blogPost['errors'] as $error) {
                    $this->flash->addMessage($error, Flash::WARNING);
                }
                if ($blogPost['saved']) {
                    $this->httpResponse->redirect('/admin/posts/' . $blogPost['entity']->getId() . '/edit');
                }
            }
        }

        $csrf = $this->generateCsrfToken();

        $this->httpResponse->renderTemplate('Backend/posts-new.html.twig', [
            'section' => 'posts',
            'blog_post' => $blogPost,
            'csrf_token' => $csrf
        ]);
    }

    public function editAction()
    {
        $postManager = $this->managers->getManagerOf('BlogPost');

        $blogPost['entity'] = $postManager->getUnique($this->route_params['id']);

        if (!$blogPost['entity']) {
            throw new \Exception("Le post n'existe pas", 404);
        }
        if (($blogPost['entity']->getUser()->getRole() == 'ROLE_ADMIN' && !$blogPost['entity']->getUser()->getBanished()) && ($blogPost['entity']->getUser()->getId() != $this->auth->getUser()->getId())) {
            $this->flash->addMessage("Vous n'êtes pas autorisé à éditer ce post.", Flash::ERROR);

            $this->httpResponse->redirect('/admin/posts');
        }

        if ($this->httpRequest->postExists('post-edit')) {
            if ($this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $blogPost = $this->processForm($blogPost['entity']);
                if (empty($blogPost['errors'])) {
                    $this->flash->addMessage('Le post a bien été modifié.');

                    $this->httpResponse->redirect('/admin/posts');
                }
                foreach ($blogPost['errors'] as $error) {
                    $this->flash->addMessage($error, Flash::WARNING);
                }

            }
        }

        var_dump($blogPost['entity']);

        $csrf = $this->generateCsrfToken();

        $this->httpResponse->renderTemplate('Backend/posts-edit.html.twig', [
            'section' => 'posts',
            'blog_post' => $blogPost,
            'csrf_token' => $csrf
        ]);
    }

    private function processForm(BlogPost $blogPost) {
        $imagesCollections = [
            'old_post_image' => $this->httpRequest->filesData('old_post_image'),
            'new_post_image' => $this->httpRequest->filesData('new_post_image')
        ];

        $blogPost->hydrate([
            'title' => $this->httpRequest->postData('title'),
            'chapo' => $this->httpRequest->postData('chapo'),
            'content' => $this->httpRequest->postData('content'),
        ]);

        $uploader = new FilesService();

        $handle['entity'] = $blogPost;
        $handle['saved'] = false;



        if ($blogPost->isValid() && empty($blogPost->getErrors())) {
            $blogPostManager =  $this->managers->getManagerOf('blogPost');
            $blogPost =$blogPostManager->save($blogPost);
            $handle['saved'] = true;

            /*
             * Process PostImages
             */
            if ($blogPost) {
                $imageManager = $this->managers->getManagerOf('postImage');
                /*
                 * Delete images
                 */
                if ($this->httpRequest->postExists('images_to_delete')) {
                    foreach ($this->httpRequest->postData('images_to_delete') as $imageToDelete) {
                        $imageDeleteRules = [
                            'target' => 'blog',
                            'folder' => '/' . $blogPost->getUser()->getId(). '/' . $blogPost->getId(),
                        ];
                        $imageToDelete = $blogPost->getImages()->getById($imageToDelete);
                        if ($imageToDelete) {
                            $isPostHero = false;
                            if ($blogPost->getHero() === $imageToDelete) {
                                $isPostHero = true;
                            }
                            $blogPost->removeImage($imageToDelete);

                            if ($uploader->deleteFile($imageDeleteRules, $imageToDelete->getUrl())) {
                                $imageManager->delete($imageToDelete->getId());
                            } else {
                                $blogPost->addImage($imageToDelete);
                                if ($isPostHero) {
                                    $blogPost->setHero($imageToDelete);
                                }

                            }
                        }
                    }
                }

                /*
                 * Add / Update images
                 */
                foreach ($imagesCollections as $imagesType=>$values) {


                    $imageUploadRules = [
                        'target' => 'blog',
                        'folder' => '/' . $blogPost->getUser()->getId(). '/' . $blogPost->getId(),
                        'maxSize' => 4,
                        'type' => 'image',
                        'minRes' => [500, 350],
                        'maxRes' => [1280, 1024]
                    ];

                    $imagesCollection = [];

                    if ($values) {

                        foreach ($values as $name=>$filesValues) {
                            foreach ($filesValues as $key=>$value) {
                                $imagesCollection[$key][$name] = $value;
                            }
                        }

                        // process Images
                        foreach ($imagesCollection as $key=>$image) {
                            $oldImage = null;

                            // Create PostImage and set Name and Post Id
                            $postImage = new PostImage([
                                'name' => $this->httpRequest->postData($imagesType)[$key]['name'],
                                'blog_post_id' => $blogPost->getId(),
                            ]);

                            // Retrieve Old Image infos
                            if ($imagesType === "old_post_image") {
                                $oldImage = $blogPost->getImages()->getById($this->httpRequest->postData('old_post_image')[$key]['id']);
                                $imageUploadRules['old'] = $oldImage->getUrl();
                                $postImage->setUrl($oldImage->getUrl());
                                $postImage->setId($oldImage->getId());
                            }

                            // Upload new Image
                            if (!empty($image['name'])){
                                $fileName = uniqid(rand(1000, 9999), true);
                                $upload = $uploader->upload($image, $imageUploadRules, $fileName);

                                if ($upload['success']) {
                                    $postImage->setUrl($upload['filename']);
                                }
                                else {
                                    foreach ($upload['errors'] as $error) {
                                        $blogPost->addCustomError('images', $error);
                                        $handle['errors'][] = "Une erreur s'est produite lors de l'upload de l'image \"" . $this->httpRequest->postData($imagesType)[$key]['name'] . "\"";
                                    }
                                }
                            }


                            if (!$postImage->getUrl()) {
                                $blogPost->addCustomError('images', "Il n'y a pas d'image \"" . $this->httpRequest->postData($imagesType)[$key]['name'] . "\"");
                                $handle['errors'][] = "Il n'y a pas d'image \"" . $this->httpRequest->postData($imagesType)[$key]['name'] . "\"";
                            } else {
                                $postImage = $imageManager->save($postImage);
                            }


                            if (isset($oldImage)) {
                                $blogPost->removeImage($oldImage);
                            }
                            $blogPost->addImage($postImage);

                            /*
                             * Process Hero
                             */
                            $oldHeroId = null;
                            //var_dump('1');
                            if ($blogPost->getHero()) {
                                //var_dump('2');
                                $oldHeroId = $blogPost->getHero()->getId();
                                //$handle['errors'][] = "Erreur d'enregistrement";
                            }
                            if ($imagesType === "old_post_image" && $postImage) {
                                if ($this->httpRequest->postData('hero') === "old-" . $postImage->getId()) {
                                    if ($oldHeroId !== $postImage->getId()) {
                                        $blogPost->setHero($postImage);
                                        $blogPostManager->save($blogPost);
                                    }
                                }
                            }
                            if ($imagesType === "new_post_image" && $postImage) {
                                if ($this->httpRequest->postData('hero') === "new-" . $key && $postImage->getId()) {
                                    $blogPost->setHero($postImage);
                                    $blogPostManager->save($blogPost);
                                }
                            }
                            //var_dump($blogPost);

                        }
                    }
                }


                $handle['entity'] = $blogPost;

            } else {
                $handle['errors'][] = "Erreur d'enregistrement";
            }
        } else {
            $handle['errors'][] = 'Formulaire non valide';
        }
        return $handle;
    }

}