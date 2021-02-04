<?php


namespace Core;



class Router
{
    /**
     * Associative array of routes (the routing table)
     * @var array
     */
    protected $routes = [];

    /**
     * Parameters from the matched route
     * @var array
     */
    protected $params = [];

    /**
     * Constructor for the router.
     * Get the routes in xml file
     */
    public function __construct()
    {

        $xml = new \DOMDocument;
        $xml->load(dirname(__DIR__) . '/config/routes.xml');

        $routes = $xml->getElementsByTagName('route');

        foreach ($routes as $route) {
            $routeUrl = $route->getAttribute('url');
            $params = $route->getElementsByTagName('param');
            $routeParams = [];
            foreach ($params as $param) {
                $routeParams[$param->getAttribute('name')] = $param->nodeValue;
            }
            $this->add($routeUrl, $routeParams);
        }
    }

    /**
     * Add a route to the routing table
     *
     * @param string $route  The route URL
     * @param array $params  Parameters (controller, action, etc...)
     *
     * @return void
     */
    public function add($route, $params = [])
    {
        // Convert the route to a regular expression : escape forward slashes
        $route = preg_replace('/\//', '\\/', $route);

        //Convert variables e.g {controller}
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);

        // Convert variables with custom regular expressions e.g. {id:\d+}
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

        // Add start and end delimiters, and case isensitive flag
        $route = '/^' . $route . '$/i';

        $this->routes[$route] = $params;
    }

    /**
     * Get all the routes from the routing table
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Match the route to the routes in the routing table, setting the $params property if a route is found.
     *
     * @param string $url  The route URL
     *
     * @return boolean  true if a match found, false otherwise
     */
    public function match($url)
    {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }

                $this->params = $params;
                return true;
            }
        }

        return false;
    }

    /**
     * Get the currently matched parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Dispatch the route, creating the controller object and running the action method
     *
     * @param HTTPRequest $request The route URL
     *
     * @throws \Exception
     *
     * @return void
     */
    public function dispatch(HTTPRequest $request)
    {
        $url = $this->removeQueryStringVariables($request->requestQueryString());

        if (!$this->match($url)) {
            throw new \Exception("La route n'a pas été trouvée", 404);
        }
        $controller = $this->params['controller'];
        $controller = $this->convertToStudlyCaps($controller);
        $controller = $this->getNamespace() . $controller;

        if (!class_exists($controller)) {
            throw new \Exception("Le controller $controller n'a pas été trouvé", 500);
        }
        $controllerObject = new $controller($this->params, $request);

        $action = $this->params['action'];
        $action = $this->convertToCamelCase($action);

        if (!preg_match('/action$/i', $action) == 0) {
            throw new \Exception("La méthode $action dans le controller $controller ne peut pas être appelée directement - Enlevez le suffix Action pour appeler cette méthode.", 500);
        }
        $controllerObject->$action();
}

    /**
     * Convert the sting with hyphens to StudlyCaps, e.g. post-authors => PostAuthors
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    protected function convertToStudlyCaps($string)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    /**
     * Convert the sting with hyphens to camelcase, e.g. add-new => addNew
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    protected function convertToCamelCase($string)
    {
        return lcfirst($this->convertToStudlyCaps($string));
    }

    /**
     * Remove the query string variable from the URL (if any).
     * As the full query string is used for the route, any variables at the end will neel to be removed before the route is matched to the routing table.
     * For example :
     *
     *   URL                           $_SERVER['QUERY_STRING']  Route
     *   -------------------------------------------------------------------
     *   localhost                     ''                        ''
     *   localhost/?                   ''                        ''
     *   localhost/?page=1             page=1                    ''
     *   localhost/posts?page=1        posts&page=1              posts
     *   localhost/posts/index         posts/index               posts/index
     *   localhost/posts/index?page=1  posts/index&page=1        posts/index
     *
     * A URL of the format localhost/?page (one variable name, no value) won't
     * work however. (NB. The .htaccess file converts the first ? to a & when
     * it's passed through to the $_SERVER variable).
     *
     * @param string $url The full URL
     *
     * @return string The URL with the query string variables removed
     */
    protected function removeQueryStringVariables($url)
    {
        if ($url != '') {
            $parts = explode('&', $url, 2);

            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }

        return $url;
    }

    /**
     * Get the namespace for the controller class.
     * The namespace defined in the route parameters is added if present.
     *
     * @return string  The request URL
     */
    protected function getNamespace()
    {
        $namespace = 'Blog\Controllers\\';

        if (array_key_exists('namespace', $this->params)) {
            $namespace.= $this->params['namespace'] . '\\';
        }

        return $namespace;
    }
}