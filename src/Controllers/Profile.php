<?php


namespace Blog\Controllers;


use Core\Controller;
use Core\HTTPResponse;

class Profile extends Controller
{
    public function showAction()
    {
        $this->requiredLogin('user');
        HTTPResponse::renderTemplate('Profile/show.html.twig');
    }
}