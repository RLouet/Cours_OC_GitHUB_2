<?php


namespace Core;


abstract class Controller
{

    /**
     * Parameters from the matched route
     * @var array
     */
    protected $route_params = [];


    /**
     * Entity managers
     * @var Managers
     */
    protected $managers = null;

    /**
     * http request
     * @var HTTPRequest
     */
    protected $httpRequest = null;


    public function __construct(array $routeParams)
    {
        $this->route_params = $routeParams;

        $this->managers = new Managers('PDO', PDOFactory::getInstance()::getPDOConnexion());
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
    public function __call(string $name, array  $args) : void
    {
        $method = $name . 'Action';

        if (method_exists($this, $method)) {
            if ($this->before() !== false) {
                call_user_func_array([$this, $method], $args);
                $this->after();
            }
        } else {
            //echo "Method $method not found in controller" . get_class($this);
            throw new \Exception("Method $method not found in controller " . get_class($this));
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
}