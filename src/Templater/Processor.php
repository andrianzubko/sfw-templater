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
     * Transforming template to page.
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    abstract public function transform(array|object $context, string $template): string;

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
