<?php


namespace App\Core;


class HTTPResponse extends ApplicationComponent
{
    protected $page;

    public function addHeader($header)
    {
        $header($header);
    }

    public function redirect($location)
    {
        header('location: '.$location);
        exit;
    }

    public function redirect404()
    {

    }

    public function send(){
        // TODO

        exit($this->page->getGeneratedPage());
    }

    /**
     * @param Page $page
     */
    public function setPage(Page $page)
    {
        $this->page = $page;
    }

    public function setCookie($name, $value = '', $expire = 0, $path = null, $domain = null, $secure = false, $httpOnly = true)
    {
        $this->setCookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }
}