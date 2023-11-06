<?php
declare(strict_types=1);

namespace SFW\Templater;

/**
 * Templates isolator.
 */
#[\AllowDynamicProperties]
class NativeIsolator extends \stdClass
{
    /**
     * Templates will be part of this class.
     */
    public function __construct(string $filename, array $context, array $properties)
    {
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }

        $this->context = $context;

        require $filename;
    }

    /**
     * With this magic method you can call anonymous functions in properties as methods.
     *
     * @throws Exception\Logic
     */
    public function __call(string $name, array $arguments): mixed
    {
        try {
            return ($this->$name)(...$arguments);
        } catch (\Throwable $e) {
            if ($e->getFile() === __FILE__) {
                throw (new Exception\Logic($e->getMessage()))
                    ->setFile($e->getTrace()[0]['file'])
                    ->setLine($e->getTrace()[0]['line']);
            } else {
                throw (new Exception\Logic($e->getMessage()))
                    ->setFile($e->getFile())
                    ->setLine($e->getLine());
            }
        }
    }
}
