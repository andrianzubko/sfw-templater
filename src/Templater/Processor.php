<?php

namespace SFW\Templater;

/**
 * Template processor.
 */
abstract class Processor
{
    /**
     * Timer of processed templates.
     */
    protected static float $timer = 0;

    /**
     * Counter of processed templates.
     */
    protected static int $counter = 0;

    /**
     * Passes parameters to properties and checking some.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(protected array $options)
    {
        if (!isset($this->options['dir'])) {
            throw new InvalidArgumentException("Option 'dir' is required");
        }
    }

    /**
     * Transforming template to page.
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    abstract public function transform(string $template, array|object|null $context = null): string;

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
