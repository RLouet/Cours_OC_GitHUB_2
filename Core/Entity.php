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

    public function setId(int $id): self
    {
        $this->id = (int) $id;
        return $this;
    }

    public function setCustomError (string $key, string $error): self
    {
        $this->errors[$key] = $error;
        return $this;
    }

    public function addCustomError (string $key, string $error): self
    {
        $this->errors[$key][] = $error;
        return $this;
    }

    public function offsetGet($var)
    {
        $method = 'get'.ucfirst(str_replace('_', '', ucwords($var, '_')));
        if (isset($this->$var) && is_callable([$this, $method]))
        {
            return $this->$method();
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
        $method = 'get'.ucfirst(str_replace('_', '', ucwords($var, '_')));
        return isset($this->$var) && is_callable([$this, $method]);
    }

    public function offsetUnset($var)
    {
        throw new \Exception('Impossible de supprimer une quelconque valeur : ' . $var);
    }
}