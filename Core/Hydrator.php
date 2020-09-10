<?php


namespace Core;



trait Hydrator
{
    public function hydrate($data)
    {
        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst(str_replace('_', '', ucwords($key, '_')));
//var_dump($method);
            if (is_callable([$this, $method])) {
                $this->$method($value);
            }
        }
    }
}
