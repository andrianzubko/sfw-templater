<?php

namespace SFW\Templater\Utility;

/**
 * HTML debugger.
 */
class HTMLDebugger extends HTMLMinifier
{
    /**
     * Leaves javascript as is.
     */
    protected static function script(string $chunk): string
    {
        return $chunk;
    }

    /**
     * Leaves styles as is.
     */
    protected static function style(string $chunk): string
    {
        return $chunk;
    }

    /**
     * Leaves comments and comment spaces that Minifier cuts out.
     */
    protected static function between(string $chunk): string
    {
        $chunk = preg_replace_callback(
            '~<[a-z][^>]+>~i', fn($M) => preg_replace('/\s+/u', ' ', $M[0]), $chunk
        );

        $chunk = preg_replace('/>(\s+)</u', '><!--\1--><', $chunk);

        $chunk = preg_replace('/^(\s+)/u', '<!--\1-->', $chunk);

        $chunk = preg_replace('/(\s+)$/u', '<!--\1-->', $chunk);

        return str_replace('--><!--', '       ', $chunk);
    }
}
