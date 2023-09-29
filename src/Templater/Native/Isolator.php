<?php

namespace SFW\Templater\Native;

use SFW\Templater\LogicException;

/**
 * Templates isolator.
 */
#[\AllowDynamicProperties]
class Isolator extends \stdClass
{
    /**
     * Templates will be part of this class.
     */
    public function __construct(array $properties, array $context, string $template)
    {
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }

        $this->context = $context;

        require $template;
    }

    /**
     * With this magic method you can call anonymous functions in properties as methods.
     *
     * @throws LogicException
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (isset($this->$name)
            && $this->$name instanceof \Closure
        ) {
            return ($this->$name)(...$arguments);
        }

        $trace = debug_backtrace(2)[0];

        throw (new LogicException("Property '$name' is not closure"))
            ->setFile($trace['file'])
            ->setLine($trace['line']);
    }
}
