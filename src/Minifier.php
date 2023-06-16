<?php

namespace SFW;

/**
 * HTML minifier.
 */
class Minifier
{
    /**
     * Minifing HTML with a special regard for <script>, <style> and <t> tags.
     *
     * Tag <t> is text which should not be touched.
     */
    public static function minify(string $contents): string
    {
        $contents = preg_replace_callback('/<!--(.*?)-->/us',
            fn($M) =>
                $M[1] === 'noindex' || $M[1] === '/noindex'
                    ? $M[0] : '',

            $contents
        );

        $parts = [];

        $regexp = '~(<script\b[^>]*>.*?</script>|<style\b[^>]*>.*?</style>|<t>.*?</t>)~uis';

        foreach (preg_split($regexp, $contents, -1, PREG_SPLIT_DELIM_CAPTURE) as $part) {
            if (preg_match('~^<(script|style|t)\b~ui', $part, $M)) {
                if ($M[1] === 't' || $M[1] === 'T') {
                    $parts[] = substr($part, 3, -4);
                } else {
                    $parts[] = preg_replace('/\s+/u', ' ', $part);
                }
            } else {
                $part = trim($part);

                if ($part !== '') {
                    $parts[] = str_replace('> <', '><',
                        preg_replace('/\s+/u', ' ', $part)
                    );
                }
            }
        }

        return implode($parts);
    }
}
