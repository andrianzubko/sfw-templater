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
     * Mime type of last template.
     */
    protected string $mime = 'text/html';

    /**
     * Possible mime types.
     */
    protected array $mimes = [
        '' => 'text/html',
        'html' => 'text/html',
        'txt' => 'text/plain',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'rtf' => 'application/rtf',
    ];

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
     * If context is an object, then only public non-static properties will be taken.
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    abstract public function transform(string $filename, array|object|null $context = null): string;

    /**
     * Normalizes context.
     */
    protected function normalizeContext(array|object|null $context): array
    {
        return is_object($context) ? get_object_vars($context) : (array) $context;
    }

    /**
     * Normalizes template filename.
     */
    protected function normalizeFilename($filename, $extension, ?string $dir = null): string
    {
        if (!str_ends_with($filename, ".$extension")) {
            $filename .= ".$extension";
        }

        if (preg_match('/\.([^.]+)\.[^.]+$/', $filename, $M)) {
            $this->mime = $this->mimes[$M[1]] ?? $this->mimes[''];
        } else {
            $this->mime = $this->mimes[''];
        }

        return isset($dir) ? "$dir/$filename" : $filename;
    }

    /**
     * Gets last template mime type.
     */
    public function getLastMime(): string
    {
        return $this->mime;
    }

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
