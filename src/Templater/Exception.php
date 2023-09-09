<?php

namespace SFW\Templater;

/**
 * Exception handler.
 */
class Exception extends \Exception
{
    /**
     * Optional file and line.
     */
    public function __construct(string $message, ?string $file = null, ?int $line = null)
    {
        parent::__construct($message);

        if (isset($file, $line)) {
            $this->file = $file;

            $this->line = $line;
        }
    }
}
