<?php

namespace SFW;

/**
 * Templater.
 */
class Templater
{
    /**
     * Some default properties.
     */
    protected static array $properties;

    /**
     * Transforming template to final page.
     */
    public static function transform(string $template, array $properties = [], bool $minify = true, ?string $timezone = null): string
    {
        if (!isset(self::$properties)) {
            self::$properties = [
                's' => function (?string $string): void {
                    echo $string ?? '';
                },
                'h' => function (?string $string): void {
                    echo htmlspecialchars($string ?? '', ENT_COMPAT, 'UTF-8');
                },
                'b' => function (?string $string): void {
                    echo nl2br(htmlspecialchars($string ?? '', ENT_COMPAT, 'UTF-8'));
                },
                'u' => function (?string $string): void {
                    echo urlencode($string ?? '');
                },
                'j' => function (?string $string): void {
                    echo str_replace(
                        ['\\','/',"\n","\r",' ','"',"'",'<','>','&',"\xe2\x80\xa8","\xe2\x80\xa9"],
                            ['\\\\','\\/','\\n','\\r','\\x20','\\x22','\\x27','\\x3C','\\x3E','\\x26','\\u2028','\\u2029'],
                                $string ?? ''
                    );
                },
                'l' => function (?string $string): void {
                    echo str_replace(
                        ['\\','/',"\n","\r",'"'],
                            ['\\\\','\\/','\\n','\\r','\\x22'],
                                $string ?? ''
                    );
                },
            ];
        }

        if (isset($timezone)) {
            $timezonePrev = date_default_timezone_get();

            if ($timezonePrev === $timezone) {
                $timezonePrev = null;
            } else {
                date_default_timezone_set($timezone);
            }
        } else {
            $timezonePrev = null;
        }

        ob_start(fn() => null);

        new Templater\Isolator($template, array_merge(self::$properties, $properties));

        $contents = ob_get_clean();

        if (isset($timezonePrev)) {
            date_default_timezone_set($timezonePrev);
        }

        if ($minify) {
            return Templater\Minifier::minify($contents);
        }

        return $contents;
    }
}
