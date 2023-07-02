<?php

namespace SFW\Templater;

/**
 * Templates isolator.
 */
#[\AllowDynamicProperties]
class Isolator
{
    /**
     * Templates will be part of this class.
     */
    public function __construct(string $template, array $properties)
    {
        foreach ($properties as $name => $value) {
            $this->{$name} = $value;
        }

        require $template;
    }

    /**
     * With this magic method you can call anonymous functions in properties as methods.
     */
    public function __call(string $name, array $arguments): mixed
    {
        return ($this->$name)(...$arguments);
    }
}
