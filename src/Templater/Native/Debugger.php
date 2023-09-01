<?php

namespace SFW\Templater\Native;

/**
 * HTML debugger.
 */
class Debugger extends Minifier
{
    /**
     * Leave javascript as is.
     */
    protected function script(string $chunk): string
    {
        return $chunk;
    }

    /**
     * Leave styles as is.
     */
    protected function style(string $chunk): string
    {
        return $chunk;
    }

    /**
     * Leave comments and comment spaces that Minifier cuts out.
     */
    protected function between(string $chunk): string
    {
        $chunk = preg_replace_callback('~<[a-z][^>]+>~ui',
            fn($M) => preg_replace('/\s+/u', ' ', $M[0]),
                $chunk
        );

        $chunk = preg_replace('/>(\s+)</u', '><!--\1--><', $chunk);

        $chunk = preg_replace('/(^\s+|\s+$)/u', '<!--\1-->', $chunk);

        return str_replace('--><!--', '       ', $chunk);
    }
}