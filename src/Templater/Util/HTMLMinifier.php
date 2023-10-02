<?php

namespace SFW\Templater\Util;

/**
 * HTML minifier.
 */
class HTMLMinifier
{
    /**
     * Minifying all.
     */
    public static function transform(string $contents): string
    {
        preg_match_all('~<(script|style|t)\b[^>]*+>.*?</\1>~is',
            $contents, $matches, flags: PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        );

        $chunks = [];

        $pos = 0;

        foreach ($matches as $M) {
            $chunks[] = static::between(
                substr($contents, $pos, (int) $M[0][1] - $pos)
            );

            $tag = strtolower($M[1][0]);

            $chunks[] = static::$tag($M[0][0]);

            $pos = (int) $M[0][1] + strlen($M[0][0]);
        }

        $chunks[] = static::between(substr($contents, $pos));

        return implode($chunks);
    }

    /**
     * Special tag <t> preserve all spaces inside, but removed itself.
     */
    protected static function t(string $chunk): string
    {
        return substr($chunk, strpos($chunk, '>') + 1, -4);
    }

    /**
     * Minifying javascript.
     */
    protected static function script(string $chunk): string
    {
        return preg_replace("/(?: |\t|\n|\r|\0|\x0B|\x0C|\u{A0}|\u{FEFF})+/S", ' ', $chunk);
    }

    /**
     * Minifying styles.
     */
    protected static function style(string $chunk): string
    {
        return preg_replace("/(?: |\t|\n|\r|\0|\x0B|\x0C|\u{A0}|\u{FEFF})+/S", ' ', $chunk);
    }

    /**
     * Minifying all between matches.
     */
    protected static function between(string $chunk): string
    {
        $chunk = trim(
            preg_replace('/<!--.*?-->/s', '', $chunk)
        );

        if ($chunk === '') {
            return '';
        }

        $chunk = preg_replace("/(?: |\t|\n|\r|\0|\x0B|\x0C|\u{A0}|\u{FEFF})+/S", ' ', $chunk);

        return str_replace('> <', '><', $chunk);
    }
}
