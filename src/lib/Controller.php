<?php
namespace Zhzy\Swow\lib;

use http\Exception\RuntimeException;

class Controller
{
    public function __call($method, $parameters)
    {
        throw new RuntimeException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}