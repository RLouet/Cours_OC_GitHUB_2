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

        if ($this->httpRequest->postExists('comment-send') && Auth::getUser()->isGranted('user')) {
            $currentComment = $this->httpRequest->postData('content');
            $comment = new Comment($this->httpRequest->postData());
            $comment->setUser(Auth::getUser())->setBlogPost($blogPost['entity'])->setValidated(Auth::getUser()->isGranted('admin'));
            if ($this->isCsrfTokenValid($this->httpRequest->postData('token'))) {
                if ($comment->isValid()) {
                    if (!$commentManager->save($comment)) {
                        Flash::addMessage('Erreur lors de l\'enregisrement de votre commentaire.', Flash::ERROR);
                    } else {
                        $message = 'Votre commentaire est enregistré.';
                        if (!Auth::getUser()->isGranted('admin')) {
                            $message .= ' Il apparaîtra bientôt, après sa validation.';
                        }
                        Flash::addMessage( $message, Flash::SUCCESS);
                        $currentComment = "";
                    }
                    //$currentComment = "";
                } else {
                    Flash::addMessage('Votre commentaire est invalide.', Flash::WARNING);
                }
            }
        }

        $comments = $commentManager->getByPost($blogPost['entity']);

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