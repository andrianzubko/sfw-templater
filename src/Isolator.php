<?php

namespace SFW;

/**
 * Templates isolator.
 */
class Isolator extends \stdClass
{
    /**
     * Templates will be part of this class.
     *
     * Special method (property if more correct) main() will be called if exists.
     */
    public function __construct(string $template, array $properties)
    {
        foreach ($properties as $name => $value) {
            $this->{$name} = $value;
        }

        require $template;

        if (isset($this->main)) {
            $this->main();
        }
    }

    /**
     * With this magic method you can call anonymous function as methods.
     */
    public function __call(string $name, array $arguments): mixed
    {
        return ($this->$name)(...$arguments);
    }
}
