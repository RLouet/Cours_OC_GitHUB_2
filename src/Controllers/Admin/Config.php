<?php


namespace Blog\Controllers\Admin;


use Blog\Entities\Blog;
use Blog\Services\FilesService;
use Core\Controller;
use Core\HTTPResponse;

class Config extends Controller
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
        $manager = $this->managers->getManagerOf('Blog');
        $blog = $blogForm['entity'] = $manager->getData();

        $flash = [
            'type' => false,
            'messages' => []
        ];

        if ($this->httpRequest->postExists('blog-update')) {
            if (!$this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $flash['type'] = 'error';
                $flash['messages'][] = 'Erreur lors de la vérification du formulaire.';
            } else {
                $blogForm = $this->processForm($blog);
                if (empty($blogForm['errors'])) {
                    $flash['type'] = 'success';
                    $flash['messages'][] = 'Les paramètres du blog ont bien été enregistrés';
                } else {
                    $flash['type'] = 'error';
                    $flash['messages'] = $blogForm['errors'];
                }

            }
        }

        $csrf = $this->generateCsrfToken();

        HTTPResponse::renderTemplate('Backend/config.html.twig', [
            'section' => 'config',
            'blog' => $blog,
            'blogForm' => $blogForm,
            'flash' => $flash,
            'csrf_token' => $csrf
        ]);
    }

    private function processForm(Blog $blog) {
        $logoUploadRules = [
            'target' => 'logo',
            'folder' => '/' . $blog->id(),
            'old' => $blog->logo(),
            'maxSize' => 1,
            'type' => 'image',
            'minRes' => [150, 60],
            'maxRes' => [300, 300]
        ];
        $cvUploadRules = [
            'target' => 'cv',
            'folder' => '/' . $blog->id(),
            'old' => $blog->cv(),
            'maxSize' => 3,
            'type' => 'pdf'
        ];

        $filesFields = [
            'logo' => [],
            'cv' => []
        ];

        $formBlog = new Blog([
            'lastname' => $this->httpRequest->postData('lastname'),
            'firstname' => $this->httpRequest->postData('firstname'),
            'email' => $this->httpRequest->postData('email'),
            'phone' => $this->httpRequest->postData('phone'),
            'teaserPhrase' => $this->httpRequest->postData('teaser_phrase'),
            'logo' => $blog->logo(),
            'cv' => $blog->cv(),
            'contactMail' => $this->httpRequest->postData('contact_mail'),
            'id' => $blog->id(),
        ]);

        $uploader = new FilesService();

        foreach ($filesFields as $filesField=>$upload) {
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
        }

        $handle['entity'] = $formBlog;

        if ($formBlog->isValid() && empty($handle['errors'])) {
            $this->managers->getManagerOf('blog')->save($formBlog);
        } else {
            $handle['errors'][] = 'Formulaire non valide';
        }
        return $handle;
    }
}