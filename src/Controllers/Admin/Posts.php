<?php


namespace Blog\Controllers\Admin;


use Blog\Entities\BlogPost;
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

        $this->httpResponse->renderTemplate('Backend/posts-view.html.twig', [
            'section' => 'posts',
            'blog_post' => $blogPost,
        ]);
    }

    public function newAction()
    {
        $blogPost['entity'] = new BlogPost(['user_id' => 1]);

        if ($this->httpRequest->postExists('post-add')) {
            if ($this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $blogPost = $this->processForm($blogPost['entity']);
                if (empty($blogPost['errors'])) {
                    Flash::addMessage('Le post a bien été enregistrés');

                    $this->httpResponse->redirect('/admin/posts');
                }
                foreach ($blogPost['errors'] as $error) {
                    Flash::addMessage($error, Flash::WARNING);
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
        if ($blogPost['entity']->getUser() != Auth::getUser()) {
            throw new \Exception("Vous n'êtes pas autorisé à éditer ce post.", 401);
        }

        if ($this->httpRequest->postExists('post-edit')) {
            if ($this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $blogPost = $this->processForm($blogPost['entity']);
                if (empty($blogPost['errors'])) {
                    Flash::addMessage('Le post a bien été modifié.');

                    $this->httpResponse->redirect('/admin/posts');
                }
                foreach ($blogPost['errors'] as $error) {
                    Flash::addMessage($error, Flash::WARNING);
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



        if ($blogPost->isValid() && empty($blogPost->getErrors())) {
            $blogPostManager =  $this->managers->getManagerOf('blogPost');
            if ($blogPost->isNew()) {
                $blogPost =$blogPostManager->save($blogPost);
            }

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
                            'folder' => '/' . $blogPost->id(),
                        ];
                        $imageToDelete = $blogPost->getImages()->getById($imageToDelete);
                        if ($imageToDelete) {
                            $isPostHero = false;
                            if ($blogPost->getHero() === $imageToDelete) {
                                $isPostHero = true;
                            }
                            $blogPost->removeImage($imageToDelete);

                            if ($uploader->deleteFile($imageDeleteRules, $imageToDelete->getUrl())) {
                                $imageManager->delete($imageToDelete->id());
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
                        'folder' => '/' . $blogPost->getId(),
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
                        //var_dump($imagesCollection);
                        foreach ($imagesCollection as $key=>$image) {
                            $oldImage = null;

                            $postImage = new PostImage([
                                'name' => $this->httpRequest->postData($imagesType)[$key]['name'],
                                'blog_post_id' => $blogPost->getId(),
                            ]);
                            if ($imagesType === "old_post_image") {
                                $oldImage = $blogPost->getImages()->getById($this->httpRequest->postData('old_post_image')[$key]['id']);
                                $imageUploadRules['old'] = $oldImage->getUrl();
                                $postImage->setUrl($oldImage->getUrl());
                                $postImage->setId($oldImage->getId());
                            }

                            if (!empty($image['name'])){
                                $fileName = uniqid(rand(1000, 9999), true);
                                $upload = $uploader->upload($image, $imageUploadRules, $fileName);

                                //var_dump($upload);

                                if ($upload['success']) {
                                    $postImage->setUrl($upload['filename']);
                                }
                                else {
                                    foreach ($upload['errors'] as $error) {
                                        $blogPost->setCustomError('image', $error);
                                    }
                                }
                            }
                            //var_dump($blogPost->getHero());
                            $postImage = $imageManager->save($postImage);
                            if ($postImage) {
                                if (isset($oldImage)) {
                                    $blogPost->removeImage($oldImage);
                                }
                                $blogPost->addImage($postImage);

                            }

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
                                if ($this->httpRequest->postData('hero') === "new-" . $key) {
                                    $blogPost->setHero($postImage);
                                    $blogPostManager->save($blogPost);
                                }
                            }

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