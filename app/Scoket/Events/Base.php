<?php
namespace App\Scoket\Events;

class Base
{
    public static function __callStatic($name, $arguments)
    {
        return (new self)->$name();
    }
}