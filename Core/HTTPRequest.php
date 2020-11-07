<?php


namespace Core;


class HTTPRequest
{
    public function cookieData($key)
    {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
    }

    public function cookieExists($key)
    {
        return isset($_COOKIE[$key]);
    }

    public function getData($key)
    {
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }

    public function getExists($key)
    {
        return isset($_GET[$key]);
    }

    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function postData(string $key = null)
    {
        if ($key) {
            return isset($_POST[$key]) ? $_POST[$key] : null;
        }
        return $_POST;
    }

    public function postExists($key)
    {
        return isset($_POST[$key]);
    }

    public function requestURI()
    {
        return $_SERVER['REQUEST_URI'];
    }

    public function requestQueryString()
    {
        return $_SERVER['QUERY_STRING'];
    }

    public function filesData($key)
    {
        return isset($_FILES[$key]) ? $_FILES[$key] : null;
    }

    public function filesExists($key)
    {
        return isset($_FILES[$key]);
    }

    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }
}