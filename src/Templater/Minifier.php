<?php

namespace SFW\Templater;

/**
 * HTML minifier.
 */
class Minifier
{
    /**
     * Minifing HTML with attention for <script>, <style> and <t> tags.
     *
     * All inside tag <t> will not be touched.
     */
    public static function minify(string $contents, bool $minify = true): string
    {
        if ($minify) {
            $contents = preg_replace_callback('/<!--(.*?)-->/us',
                fn($M) =>
                    $M[1] === 'noindex' || $M[1] === '/noindex'
                        ? $M[0] : '',

                $contents
            );
        }

        $parts = [];

        $regexp = '~(<script\b[^>]*>.*?</script>|<style\b[^>]*>.*?</style>|<t>.*?</t>)~uis';

        foreach (preg_split($regexp, $contents, -1, PREG_SPLIT_DELIM_CAPTURE) as $part) {
            if (preg_match('~^<(script|style|t)\b~ui', $part, $M)) {
                if ($M[1] === 't' || $M[1] === 'T') {
                    $parts[] = substr($part, 3, -4);
                } elseif ($minify) {
                    $parts[] = preg_replace('/\s+/u', ' ', $part);
                } else {
                    $parts[] = $part . "\n";
                }
            } elseif ($minify) {
                $part = trim($part);

                if ($part !== '') {
                    $parts[] = str_replace('> <', '><',
                        preg_replace('/\s+/u', ' ', $part)
                    );
                }
            } else {
                $parts[] = $part;
            }
        }

        return implode($parts);
    }
}
