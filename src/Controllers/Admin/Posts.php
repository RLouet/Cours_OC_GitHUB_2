<?php


namespace Blog\Controllers\Admin;


use Blog\Entities\BlogPost;
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

    public function new()
    {
        $manager = $this->managers->getManagerOf('Blog');
        $blog = $manager->getData();

        $flash = [
            'type' => false,
            'messages' => []
        ];

        $blogPost = new BlogPost();

        if ($this->httpRequest->postExists('post-add')) {
            if (!$this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $flash['type'] = 'error';
                $flash['messages'][] = 'Erreur lors de la vérification du formulaire.';
            } else {
                $blogPost = $this->processForm();
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

    private function processForm() {
        /*$imageUploadRules = [
            'target' => 'logo',
            'folder' => '/' . $blog->id(),
            'old' => $blog->logo(),
            'maxSize' => 1,
            'type' => 'image',
            'minRes' => [150, 60],
            'maxRes' => [300, 300]
        ];*/

        $filesFields = [
            'logo' => [],
            'cv' => []
        ];

        $blogPost = new BlogPost([
            'user_id' => 1,
            'title' => $this->httpRequest->postData('title'),
            'edit_date' => new \DateTime(),
            'chapo' => $this->httpRequest->postData('chapo'),
            'content' => $this->httpRequest->postData('content'),
        ]);

        $uploader = new FilesService();

        /*foreach ($filesFields as $filesField=>$upload) {
            if (!empty($this->httpRequest->filesData($filesField)['name'])){
                $fileName = ${$filesField . "UploadRules"}['target'] . "-" . $formBlog->firstname() . "_" . $formBlog->lastname();
                $upload = $uploader->upload($this->httpRequest->filesData($filesField), ${$filesField . "UploadRules"}, $fileName);

                if ($upload['success']) {
                    $method = 'set' . ucfirst($filesField);
                    $formBlog->$method($upload['filename']);
                }
                else {
                    foreach ($upload['errors'] as $error) {
                        $formBlog->setCustomError($filesField, $error);
                    }
                }
            }
        }*/

        $handle['entity'] = $blogPost;

        if ($blogPost->isValid() && empty($blogPost->errors())) {
            $this->managers->getManagerOf('blogPost')->save($blogPost);
        } else {
            $handle['errors'][] = 'Formulaire non valide';
        }
        return $handle;
    }

}