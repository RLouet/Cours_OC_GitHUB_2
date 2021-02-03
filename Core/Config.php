<?php


namespace Core;


use DOMDocument;

class Config
{
    private static ?Config $instance = null;
    private array $vars = [];

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
        if(self::$instance === null)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function get(string $var)
    {
        if (isset($this->vars[$var]))
        {
            return $this->vars[$var];
        }

        return null;
    }
}