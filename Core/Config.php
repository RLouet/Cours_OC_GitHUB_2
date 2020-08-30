<?php


namespace Core;


use \DOMDocument;

class Config
{
    private static $instance = null;
    private $vars = [];

    private function __construct()
    {
        $xml = new DOMDocument;
        $xml->load(__DIR__.'/../config/config.xml');

        $elements = $xml->getElementsByTagName('define');

        foreach ($elements as $element)
        {
            $this->vars[$element->getAttribute('var')] = $element->getAttribute('value');
        }
    }

    public static function getInstance()
    {
        if(is_null(self::$instance))
        {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function get($var)
    {
        if (isset($this->vars[$var]))
        {
            return $this->vars[$var];
        }

        return null;
    }
}