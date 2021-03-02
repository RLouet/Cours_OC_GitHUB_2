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
        $comment = new Comment();

        if ($this->httpRequest->postExists('comment-send') && $this->auth->getUser()->isGranted('user')) {
            $comment
                ->setUser($this->auth->getUser())
                ->setContent($this->httpRequest->postData('content'))
                ->setBlogPost($blogPost['entity'])
                ->setValidated($this->auth->getUser()
                    ->isGranted('admin')
                )
            ;
            if ($this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $messageFlash = ['message' => "Votre commentaire est invalide.", 'type' => Flash::WARNING];
                if ($comment->isValid()) {

                    if ($commentManager->save($comment)) {
                        $message = 'Votre commentaire est enregistré.';
                        if (!$this->auth->getUser()->isGranted('admin')) {
                            $message .= ' Il apparaîtra bientôt, après sa validation.';
                        }
                        $this->flash->addMessage( $message, Flash::SUCCESS);
                        $this->httpResponse->redirect('/admin/posts/' . $blogPost['entity']->getId() . '/view');
                    }
                    $messageFlash = ['message' => "Erreur lors de l'enregistrement de votre commentaire.", 'type' => Flash::ERROR];
                }
                $this->flash->addMessage($messageFlash['message'], $messageFlash['type']);
            }
        }

        $comments = $commentManager->getByPost($this->auth->getUser(), $blogPost['entity']->getId());

        $csrf = $this->generateCsrfToken();

        $this->httpResponse->renderTemplate('Backend/posts-view.html.twig', [
            'section' => 'posts',
            'blog_post' => $blogPost,
            'comments' => $comments,
            'current_comment' => $comment,
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

        $csrf = $this->generateCsrfToken();

        $this->httpResponse->renderTemplate('Backend/posts-edit.html.twig', [
            'section' => 'posts',
            'blog_post' => $blogPost,
            'csrf_token' => $csrf
        ]);
    }

    private function processForm(BlogPost $blogPost) {
        $blogPost->hydrate([
            'title' => $this->httpRequest->postData('title'),
            'chapo' => $this->httpRequest->postData('chapo'),
            'content' => $this->httpRequest->postData('content'),
        ]);

        $handle = [
            'entity' => $blogPost,
            'saved' => false,
            'errors' => []
        ];
        $handle['entity'] = $blogPost;
        $handle['saved'] = false;

        if (!$blogPost->isValid()) {
            $handle['errors'][] = 'Formulaire non valide';
            return $handle;
        }
        $blogPostManager =  $this->managers->getManagerOf('blogPost');
        $blogPost = $blogPostManager->save($blogPost);
        $handle['saved'] = true;

        /*
         * Process PostImages
         */
        if (!$blogPost) {
            $handle['errors'][] = "Erreur d'enregistrement";
            return $handle;
        }

        /*
         * Delete images
         */
        if ($this->httpRequest->postExists('images_to_delete')) {
            $blogPost = $this->deletePostImages($blogPost);
        }

        /*
         * Add / Update images
         */

        $imagesCollections = [
            'old_post_image' => $this->httpRequest->filesData('old_post_image'),
            'new_post_image' => $this->httpRequest->filesData('new_post_image')
        ];

        foreach ($imagesCollections as $imagesType=>$values) {
            $return = $this->processImagesCollection($blogPost, $imagesType, $values);
            $blogPost = $return['entity'];
            $handle['errors'] = array_merge($handle['errors'], $return['errors']);

        }
        $handle['entity'] = $blogPost;
        return $handle;
    }

    private function deletePostImages(BlogPost $blogPost)
    {
        $imageDeleteRules = [
            'target' => 'blog',
            'folder' => '/' . $blogPost->getUser()->getId(). '/' . $blogPost->getId(),
        ];

        $uploader = new FilesService();
        $imageManager = $this->managers->getManagerOf('postImage');

        foreach ($this->httpRequest->postData('images_to_delete') as $imageToDelete) {
            $imageToDelete = $blogPost->getImages()->getById($imageToDelete);
            if ($imageToDelete) {
                $isPostHero = false;
                if ($blogPost->getHero() === $imageToDelete) {
                    $isPostHero = true;
                }
                $blogPost->removeImage($imageToDelete);

                if ($uploader->deleteFile($imageDeleteRules, $imageToDelete->getUrl())) {
                    $imageManager->delete($imageToDelete->getId());
                    continue;
                }
                $blogPost->addCustomError('images', "Error lors de la suppressions de l'image \"" . $imageToDelete->getName() . "\"");
                $blogPost->addImage($imageToDelete);
                if ($isPostHero) {
                    $blogPost->setHero($imageToDelete);
                }

            }
        }
        return $blogPost;
    }

    private function processImagesCollection(BlogPost $blogPost, string $imagesType, ?array $values)
    {
        $imagesCollection = [];

        $return['errors'] = [];

        if ($values) {
            foreach ($values as $name=>$filesValues) {
                foreach ($filesValues as $key=>$value) {
                    $imagesCollection[$key][$name] = $value;
                }
            }

            // process Images
            foreach ($imagesCollection as $key=>$image) {
                $processImageReturn = $this->processImage($blogPost, $imagesType, $key, $image);
                $blogPost = $processImageReturn['entity'];
                $return['errors'] = array_merge($return['errors'], $processImageReturn['errors']);
            }
        }
        $return['entity'] = $blogPost;
        return $return;
    }

    private function processImage(BlogPost $blogPost, string $imagesType, string $key, array $image)
    {
        $return = [
            'entity' => $blogPost,
            'errors' => []
        ];

        $imageUploadRules = [
            'target' => 'blog',
            'folder' => '/' . $blogPost->getUser()->getId() . '/' . $blogPost->getId(),
            'maxSize' => 4,
            'type' => 'image',
            'minRes' => [500, 350],
            'maxRes' => [1280, 1024]
        ];
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

        $uploadReturn = $this->uploadImage($postImage, $image,  $blogPost, $imageUploadRules);
        $blogPost = $uploadReturn['blog_post'];
        $postImage = $uploadReturn['post_image'];
        $return['errors'] = $uploadReturn['errors'];

        // Remove old and add new image
        if (isset($oldImage)) {
            $blogPost->removeImage($oldImage);
        }
        $blogPost->addImage($postImage);

        // Process Hero
        if ($postImage) {
            $blogPost = $this->processHero($blogPost, $imagesType, $postImage, $key);
        }
        $return['entity'] = $blogPost;
        return $return;
    }

    private function uploadImage(PostImage $postImage, array $image, BlogPost $blogPost, array $imageUploadRules) {
        $return = [
            'post_image' => $postImage,
            'blog_post' => $blogPost,
            'errors' => []
        ];
        if (!$postImage->getErrors() && !empty($image['name'])){
            $uploader = new FilesService();
            $fileName = uniqid(rand(1000, 9999), true);
            $upload = $uploader->upload($image, $imageUploadRules, $fileName);

            if ($upload['success']) {
                $postImage->setUrl($upload['filename']);
            }
            foreach ($upload['errors'] as $error) {
                $postImage->addCustomError('file', $error);
                $blogPost->addCustomError('images', $error);
            }
            $return['post_image'] = $postImage;
            $return['blog_post'] = $blogPost;
        }

        if (!$postImage->getUrl()) {
            $return['blog_post'] = $blogPost->addCustomError('images', "Il n'y a pas d'image \"" . $this->httpRequest->postData($imagesType)[$key]['name'] . "\"");
            $return['errors'][] = "Il n'y a pas d'image \"" . $this->httpRequest->postData($imagesType)[$key]['name'] . "\"";
            return $return;
        }
        if ($postImage->isValid()) {
            $imageManager = $this->managers->getManagerOf('postImage');
            $return['post_image'] = $imageManager->save($postImage);
            return $return;
        }

        $return['blog_post'] = $blogPost->addCustomError('images', "L'image \"" . $this->httpRequest->postData($imagesType)[$key]['name'] . "\" n'est pas valide.");
        $return['errors'][] = "L'image \"" . $this->httpRequest->postData($imagesType)[$key]['name'] . "\" n'est pas valide.";
        return $return;
    }

    private function processHero(BlogPost $blogPost, string $imagesType, PostImage $postImage, $key)
    {
        $oldHeroId = null;
        if ($blogPost->getHero()) {
            $oldHeroId = $blogPost->getHero()->getId();
        }

        $blogPostManager =  $this->managers->getManagerOf('blogPost');
        if ($imagesType === "old_post_image") {
            if ($this->httpRequest->postData('hero') === "old-" . $postImage->getId()) {
                if ($oldHeroId !== $postImage->getId()) {
                    $blogPost->setHero($postImage);
                    $blogPostManager->save($blogPost);
                }
            }
        }
        if ($imagesType === "new_post_image") {
            if ($this->httpRequest->postData('hero') === "new-" . $key && $postImage->getId()) {
                $blogPost->setHero($postImage);
                $blogPostManager->save($blogPost);
            }
        }
        return $blogPost;
    }

}