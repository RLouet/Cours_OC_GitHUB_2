<?php


namespace Blog\Controllers;


use Blog\Entities\Comment;
use Core\Auth;
use Core\Config;
use Core\Controller;
use Core\Flash;
use Core\HTTPResponse;

class Book extends Controller
{
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

         //var_dump($homeData);

        $this->httpResponse->renderTemplate('Frontend/blog.html.twig', [
            'section' => 'book',
            'posts' => $posts,
        ]);
    }

    /**
     * Show single post
     *
     * @return void
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function viewAction()
    {
        /*$flash = [
            'type' => false,
            'messages' => []
        ];*/

        $postManager = $this->managers->getManagerOf('BlogPost');
        $blogPost['entity'] = $postManager->getUnique($this->route_params['id']);
        $commentManager = $this->managers->getManagerOf('comment');

        $currentComment = "";

        if ($this->httpRequest->postExists('comment-send') && $this->auth->getUser()->isGranted('user')) {
            $currentComment = $this->httpRequest->postData('content');
            $comment = new Comment($this->httpRequest->postData());
            $comment->setUser($this->auth->getUser())->setBlogPost($blogPost['entity'])->setValidated($this->auth->getUser()->isGranted('admin'));
            if ($this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                $messageFlash = ['message' => "Votre commentaire est invalide.", 'type' => Flash::WARNING];
                if ($comment->isValid()) {
                    $messageFlash = ['message' => "Erreur lors de l'enregistrement de votre commentaire.", 'type' => Flash::ERROR];

                    if ($commentManager->save($comment)) {
                        $message = 'Votre commentaire est enregistré.';
                        if (!$this->auth->getUser()->isGranted('admin')) {
                            $message .= ' Il apparaîtra bientôt, après sa validation.';
                        }
                        $currentComment = '';
                        $messageFlash = ['message' => $message, 'type' => Flash::SUCCESS];
                    }
                }
                $this->flash->addMessage($messageFlash['message'], $messageFlash['type']);
            }
        }

        $comments = $commentManager->getByPost($this->auth->getUser(), $blogPost['entity']->getId());

        $csrf = $this->generateCsrfToken();

        $this->httpResponse->renderTemplate('Frontend/post-view.html.twig', [
            'section' => 'book',
            'blog_post' => $blogPost,
            'comments' => $comments,
            'current_comment' => $currentComment,
            //'flash' => $flash,
            'csrf_token' => $csrf
        ]);
    }
}