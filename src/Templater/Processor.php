<?php

namespace SFW\Templater;

/**
 * Template processor.
 */
abstract class Processor
{
    /**
     * Default properties.
     */
    protected array $properties = [];

    /**
     * Timer of processed templates.
     */
    protected static float $timer = 0;

    /**
     * Counter of processed templates.
     */
    protected static int $counter = 0;

    /**
     * Adding property.
     */
    public function addProperty(string $name, mixed $value): void
    {
        $this->properties[$name] = $value;
    }

    /**
     * Adding properties.
     */
    public function addProperties(array $properties): void
    {
        foreach ($properties as $name => $value) {
            $this->properties[$name] = $value;
        }
    }

    /**
     * Transforming template to page.
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    abstract public function transform(array $e, string $template): string;

    /**
     * Getting timer of processed templates.
     */
    public function getTimer(): float
    {
        return self::$timer;
    }

    /**
     * Getting count of processed templates.
     */
    public function getCounter(): int
    {
        return self::$counter;
    }
}
