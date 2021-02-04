<?php


namespace Core;


abstract class Entity implements \ArrayAccess
{

    use Hydrator;

    protected array $errors = [];
    protected ?int $id = null;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    public function isNew(): bool
    {
        return empty($this->id);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = (int) $id;
    }

    public function setCustomError (string $key, string $error) {
        $this->errors[$key] = $error;
    }

    public function offsetGet($var)
    {
        if (isset($this->$var) && is_callable([$this, $var]))
        {
            return $this->$var();
        }
    }

    public function offsetSet($var, $value)
    {
        $method = 'set'.ucfirst(str_replace('_', '', ucwords($var, '_')));

        if (isset($this->$var) && is_callable([$this, $method]))
        {
            $this->$method($value);
        }
    }

    public function offsetExists($var)
    {
        return isset($this->$var) && is_callable([$this, $var]);
    }

    public function offsetUnset($var)
    {
        throw new \Exception('Impossible de supprimer une quelconque valeur : ' . $var);
    }
}