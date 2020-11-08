<?php


namespace Blog\Controllers\Admin;


use Blog\Entities\Blog;
use Blog\Services\FilesService;
use Core\Controller;
use Core\Flash;
use Core\HTTPResponse;

class Config extends Controller
{

    /**
     * Before filter
     */
    protected function before(): void
    {
        $this->requiredLogin('admin');
    }

    /**
     * After filter
     */
    protected function after(): void
    {
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
        $manager = $this->managers->getManagerOf('Blog');
        $blog = $blogForm['entity'] = $manager->getData();

        if ($this->httpRequest->postExists('blog-update')) {
            if ($this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $blogForm = $this->processForm($blog);
                if (empty($blogForm['errors'])) {
                    Flash::addMessage('Les paramètres du blog ont bien été enregistrés');
                } else {
                    foreach ($blogForm['errors'] as $error) {
                        Flash::addMessage($error, Flash::WARNING);
                    }
                }
            }
        }

        $csrf = $this->generateCsrfToken();

        HTTPResponse::renderTemplate('Backend/config.html.twig', [
            'section' => 'config',
            'blog' => $blog,
            'blogForm' => $blogForm,
            'csrf_token' => $csrf
        ]);
    }

    private function processForm(Blog $blog) {
        $logoUploadRules = [
            'target' => 'logo',
            'folder' => '/' . $blog->getId(),
            'old' => $blog->getLogo(),
            'maxSize' => 1,
            'type' => 'image',
            'minRes' => [150, 60],
            'maxRes' => [300, 300]
        ];
        $cvUploadRules = [
            'target' => 'cv',
            'folder' => '/' . $blog->getId(),
            'old' => $blog->getCv(),
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
            'logo' => $blog->getLogo(),
            'cv' => $blog->getCv(),
            'contactMail' => $this->httpRequest->postData('contact_mail'),
            'id' => $blog->getId(),
        ]);

        $uploader = new FilesService();

        foreach ($filesFields as $filesField=>$upload) {
            if (!empty($this->httpRequest->filesData($filesField)['name'])){
                $fileName = ${$filesField . "UploadRules"}['target'] . "-" . $formBlog->getfirstname() . "_" . $formBlog->getLastname();
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