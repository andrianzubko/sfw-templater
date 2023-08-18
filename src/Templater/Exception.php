<?php

namespace SFW\Templater;

/**
 * Exception handler.
 */
class Exception extends \Exception
{
    /**
     * Correct file and line.
     */
    public function __construct(string $message, ?string $file = null, ?int $line = null)
    {
        parent::__construct($message);

        if (isset($file, $line)) {
            $this->file = $file;

            $this->line = $line;
        } else {
            foreach ($this->getTrace() as $trace) {
                if (!str_starts_with($trace['file'], dirname(__DIR__))) {
                    $this->file = $trace['file'];

                    $this->line = $trace['line'];

                    break;
                }
            }
        }
    }
}
