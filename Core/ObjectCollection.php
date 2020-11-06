<?php


namespace Core;

use SplObjectStorage;


class ObjectCollection extends SplObjectStorage
{
    public function getById(int $id)
    {
        $this->rewind();
        while ($this->valid()) {
            $object = $this->current();
            if ($object->id() === $id) {
                $this->rewind();
                return $object;
            }
            $this->next();
        }
        return null;
    }
}