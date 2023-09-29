<?php

namespace SFW\Templater\Native;

/**
 * HTML minifier.
 */
class Minifier
{
    /**
     * Minifying all.
     */
    public function transform(string $contents): string
    {
        preg_match_all('~<(script|style|t)\b[^>]*+>.*?</\1>~is',
            $contents, $matches, flags: PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        );

        $chunks = [];

        $pos = 0;

        foreach ($matches as $M) {
            $chunks[] = $this->between(
                substr($contents, $pos, (int) $M[0][1] - $pos)
            );

            $tag = strtolower($M[1][0]);

            $chunks[] = $this->$tag($M[0][0]);

            $pos = (int) $M[0][1] + strlen($M[0][0]);
        }

        $chunks[] = $this->between(substr($contents, $pos));

        return implode($chunks);
    }

    /**
     * Special tag <t> preserve all spaces inside, but removed itself.
     */
    protected function t(string $chunk): string
    {
        return substr($chunk, strpos($chunk, '>') + 1, -4);
    }

    /**
     * Minifying javascript.
     */
    protected function script(string $chunk): string
    {
        return preg_replace("/(?: |\t|\n|\r|\0|\x0B|\x0C|\u{A0}|\u{FEFF})+/S", ' ', $chunk);
    }

    /**
     * Minifying styles.
     */
    protected function style(string $chunk): string
    {
        return preg_replace("/(?: |\t|\n|\r|\0|\x0B|\x0C|\u{A0}|\u{FEFF})+/S", ' ', $chunk);
    }

    /**
     * Minifying all between matches.
     */
    protected function between(string $chunk): string
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
