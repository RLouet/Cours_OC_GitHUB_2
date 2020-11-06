<?php


namespace Blog\Controllers\Admin;


use Blog\Entities\BlogPost;
use Blog\Entities\PostImage;
use Blog\Services\FilesService;
use Core\Controller;
use Core\HTTPResponse;

class Posts extends Controller
{

    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        //echo '<p>(before)</p>';
        //return false;
    }

    /**
     * After filter
     *
     * @return void
     */
    protected function after()
    {
        //echo '<p>(after)</p>';
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
    public function index()
    {
        $blogManager = $this->managers->getManagerOf('Blog');
        $blog = $blogManager->getData();
        $postManager = $this->managers->getManagerOf('BlogPost');
        $posts = $postManager->getList();

        //var_dump($posts);

        HTTPResponse::renderTemplate('Backend/posts-index.html.twig', [
            'section' => 'posts',
            'blog' => $blog,
            'posts' => $posts,
        ]);
    }

    public function view()
    {
        $blogManager = $this->managers->getManagerOf('Blog');
        $blog = $blogManager->getData();

        $flash = [
            'type' => false,
            'messages' => []
        ];

        $postManager = $this->managers->getManagerOf('BlogPost');

        $blogPost['entity'] = $postManager->getUnique($this->route_params['id']);

        HTTPResponse::renderTemplate('Backend/posts-view.html.twig', [
            'section' => 'posts',
            'blog' => $blog,
            'blog_post' => $blogPost,
            'flash' => $flash
        ]);
    }

    public function new()
    {
        $manager = $this->managers->getManagerOf('Blog');
        $blog = $manager->getData();

        $flash = [
            'type' => false,
            'messages' => []
        ];

        $blogPost['entity'] = new BlogPost(['user_id' => 1]);

        if ($this->httpRequest->postExists('post-add')) {
            if (!$this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $flash['type'] = 'error';
                $flash['messages'][] = 'Erreur lors de la vérification du formulaire.';
            } else {
                $blogPost = $this->processForm($blogPost['entity']);
                if (empty($blogPost['errors'])) {
                    $flash['type'] = 'success';
                    $flash['messages'][] = 'Le post a bien été enregistrés';

                    $postManager = $this->managers->getManagerOf('BlogPost');
                    $posts = $postManager->getList();

                    HTTPResponse::renderTemplate('Backend/posts-index.html.twig', [
                        'section' => 'posts',
                        'blog' => $blog,
                        'posts' => $posts,
                        'flash' => $flash,
                    ]);
                    exit();
                } else {
                    //var_dump($blogPost);
                    $flash['type'] = 'error';
                    $flash['messages'] = $blogPost['errors'];
                }

            }
        }

        $csrf = $this->generateCsrfToken();

        HTTPResponse::renderTemplate('Backend/posts-new.html.twig', [
            'section' => 'posts',
            'blog' => $blog,
            'blog_post' => $blogPost,
            'flash' => $flash,
            'csrf_token' => $csrf
        ]);
    }

    public function edit()
    {
        $blogManager = $this->managers->getManagerOf('Blog');
        $blog = $blogManager->getData();

        $flash = [
            'type' => false,
            'messages' => []
        ];

        $postManager = $this->managers->getManagerOf('BlogPost');

        $blogPost['entity'] = $postManager->getUnique($this->route_params['id']);

        //var_dump($blogPost['entity']->getImages());

        //var_dump($_POST['old_post_image'], $_FILES['old_post_image']);

        if ($this->httpRequest->postExists('post-edit')) {
            if (!$this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $flash['type'] = 'error';
                $flash['messages'][] = 'Erreur lors de la vérification du formulaire.';
            } else {
                $blogPost = $this->processForm($blogPost['entity']);
                if (empty($blogPost['errors'])) {
                    $flash['type'] = 'success';
                    $flash['messages'][] = 'Le post a bien été modifié.';

                    $posts = $postManager->getList();

                    HTTPResponse::renderTemplate('Backend/posts-index.html.twig', [
                        'section' => 'posts',
                        'blog' => $blog,
                        'posts' => $posts,
                        'flash' => $flash,
                    ]);
                    exit();
                } else {
                    $flash['type'] = 'error';
                    $flash['messages'] = $blogPost['errors'];
                }

            }
        }

        $csrf = $this->generateCsrfToken();

        HTTPResponse::renderTemplate('Backend/posts-edit.html.twig', [
            'section' => 'posts',
            'blog' => $blog,
            'blog_post' => $blogPost,
            'flash' => $flash,
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

        //var_dump($_POST);

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
                        'folder' => '/' . $blogPost->id(),
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
                                'blog_post_id' => $blogPost->id(),
                            ]);
                            if ($imagesType === "old_post_image") {
                                $oldImage = $blogPost->getImages()->getById($this->httpRequest->postData('old_post_image')[$key]['id']);
                                $imageUploadRules['old'] = $oldImage->getUrl();
                                $postImage->setUrl($oldImage->getUrl());
                                $postImage->setId($oldImage->id());
                                //var_dump($key,$this->httpRequest->postData('old_post_image')[$key]['id']);
                            }

                            if (!empty($image['name'])){
                                //var_dump($imageUploadRules);
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
                            if ($blogPost->getHero()) {
                                $oldHeroId = $blogPost->getHero()->id();
                            }
                            if ($imagesType === "old_post_image" && $postImage) {
                                if ($this->httpRequest->postData('hero') === "old-" . $postImage->id()) {
                                    if ($oldHeroId !== $postImage->id()) {
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