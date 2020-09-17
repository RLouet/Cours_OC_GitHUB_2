<?php


namespace Blog\Controllers\Admin;


use Blog\Entities\Blog;
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
        /*$config = new Config();
        echo $config->get('show_errors');*/

        /*$manager = $this->managers->getManagerOf('Blog');
        $blog = new Blog();
        $manager->save($blog);*/
        if ($this->httpRequest->postExists('blog-update')) {
            $this->processForm();
        }

        HTTPResponse::renderTemplate('Backend/config.html.twig', [
            'section' => 'config',
        ]);
    }

    private function processForm() {
        $blog = new Blog([
            'lastname' => $this->httpRequest->postData('lastname'),
            'firstname' => $this->httpRequest->postData('firstname'),
            'email' => $this->httpRequest->postData('email'),
            'phone' => $this->httpRequest->postData('phone'),
            'teaserPhrase' => $this->httpRequest->postData('teaser_phrase'),
            'logo' => $this->httpRequest->postData('logo'),
            'cv' => $this->httpRequest->postData('cv'),
            'contactMail' => $this->httpRequest->postData('contact_mail'),
            'id' => "1",
        ]);
        //var_dump($blog);

        if ($blog->isValid()) {
            $this->managers->getManagerOf('blog')->save($blog);
        } else {
            throw new \Exception('formulaire invalid');
        }
    }
}