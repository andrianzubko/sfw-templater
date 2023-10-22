<?php

namespace SFW\Templater\Utility;

/**
 * HTML minifier.
 */
class HTMLMinifier
{
    /**
     * Minifies all.
     */
    public static function transform(string $contents): string
    {
        preg_match_all('~<(script|style|t)\b[^>]*+>.*?</\1>~is', $contents, $matches,
            flags: PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        );

        $chunks = [];

        $pos = 0;

        foreach ($matches as $M) {
            $chunks[] = static::between(substr($contents, $pos, (int) $M[0][1] - $pos));

            $tag = strtolower($M[1][0]);

            $chunks[] = static::$tag($M[0][0]);

            $pos = (int) $M[0][1] + \strlen($M[0][0]);
        }

        $chunks[] = static::between(substr($contents, $pos));

        $chunks[] = "\n";

        return implode($chunks);
    }

    /**
     * Special tag <t> preserves all spaces inside, but removes itself.
     */
    protected static function t(string $chunk): string
    {
        return substr($chunk, strpos($chunk, '>') + 1, -4);
    }

    /**
     * Minifies javascript.
     */
    protected static function script(string $chunk): string
    {
        return preg_replace("/[ \t\n\r\v\f\0]+/", ' ', $chunk);
    }

    /**
     * Minifies styles.
     */
    protected static function style(string $chunk): string
    {
        return preg_replace("/[ \t\n\r\v\f\0]+/", ' ', $chunk);
    }

    /**
     * Minifies all between matches.
     */
    protected static function between(string $chunk): string
    {
        $chunk = trim(preg_replace('/<!--.*?-->/s', '', $chunk));

        if ($chunk === '') {
            return '';
        }

        return str_replace('> <', '><', preg_replace("/[ \t\n\r\v\f\0]+/", ' ', $chunk));
    }
}
