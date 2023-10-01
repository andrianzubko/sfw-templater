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
        try {
            return ($this->$name)(...$arguments);
        } catch (\Throwable $e) {
            if ($e->getFile() === __FILE__) {
                throw (new LogicException($e->getMessage()))
                    ->setFile($e->getTrace()[0]['file'])
                    ->setLine($e->getTrace()[0]['line']);
            } else {
                throw (new LogicException($e->getMessage()))
                    ->setFile($e->getFile())
                    ->setLine($e->getLine());
            }
        }
    }
}
