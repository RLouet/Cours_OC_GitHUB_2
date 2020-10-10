<?php


namespace Core;


class Form
{
    public function test(string $input)
    {
        $return = "<p>" . $input . "</p>";
        return $return;
    }
}