<?php


namespace Core;


abstract class Controller
{
    use CsrfTokenManager;

    /**
     * Parameters from the matched route
     * @var array
     */
    protected array $route_params = [];


    /**
     * Entity managers
     * @var Managers
     */
    protected Managers $managers;

    /**
     * http request
     * @var HTTPRequest
     */
    protected HTTPRequest $httpRequest;

    /**
     * http response
     * @var HTTPResponse
     */
    protected HTTPResponse $httpResponse;

    /**
     * auth
     * @var Auth
     */
    protected Auth $auth;

    /**
     * flash
     * @var Flash
     */
    protected Flash $flash;

    public function __construct(array $routeParams, HTTPRequest $request)
    {
       $this->httpRequest = $request;
       $this->httpResponse = new HTTPResponse();

        $this->route_params = $routeParams;

        $this->managers = new Managers('PDO', PDOFactory::getPDOConnexion());
        $this->auth = Auth::getInstance();
        $this->flash = Flash::getInstance();
    }

    /**
     * Magic method called when a non-existant or inaccessible method is called on an object of this class.
     * Used to execute before and after filter methods on action methods.
     * Action methods need to be named with an "Action" suffix, E.G. indexAction, showAction etc.
     *
     * @param string $name  Method name
     * @param array  $args Arguments passed to the method
     *
     * @throws \Exception if method not found
     *
     * @return void
     */
    public function __call(string $name, array  $args): void
    {
        $method = $name . 'Action';

        if (!method_exists($this, $method)) {
            throw new \Exception("La méthode $method n'a pas été trouvée" . get_class($this), 404);
        }
        if ($this->before() !== false) {
            call_user_func_array([$this, $method], $args);
            $this->after();
        }
    }

    /**
     * Before filter - Called before an action method.
     *
     * @return void
     */
    protected function before()
    {

    }

    /**
     * After filter - Called after an action method.
     *
     * @return void
     */
    protected function after()
    {

    }

    /**
     * Require the user to be logged.
     * Remember the requested page then redirect on it
     *
     * @param string $role  Role required
     */
    public function requiredLogin(string $role = 'user'): void
    {
        if (!$this->auth->getUser() || !$this->auth->getUser()->isGranted($role)) {
            $this->flash->addMessage("Vous n'avez pas les droits pour accéder à cette page.", Flash::WARNING);
            $this->auth->rememberRequestedPage();
            $this->httpResponse->redirect('/login');
        }
    }
}