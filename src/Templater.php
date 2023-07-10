<?php

namespace SFW;

/**
 * Templater.
 */
class Templater
{
    /**
     * Default properties.
     */
    protected array $properties;

    /**
     * Initializing default properties.
     */
    public function __construct()
    {
        $this->properties = [
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

    /**
     * Adding property.
     */
    public function addProperty(string $name, mixed $value): void
    {
        $this->properties[$name] = $value;
    }

    /**
     * Adding properties.
     */
    public function addProperties(array $properties): void
    {
        foreach ($properties as $name => $value) {
            $this->properties[$name] = $value;
        }
    }

    /**
     * Transforming template to page.
     *
     * Method main() (closure in property) will be called if exists in some template.
     */
    public function transform(array $e, string $template, bool $minify = true, ?string $timezone = null): string
    {
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

        $isolator = new Templater\Isolator($template,
            array_merge($this->properties,
                [
                    'e' => $e,
                ]
            )
        );

        ob_clean();

        if ($isolator->main ?? null instanceof \Closure) {
            $isolator->main();
        }

        $contents = ob_get_clean();

        if (isset($timezonePrev)) {
            date_default_timezone_set($timezonePrev);
        }

        return Templater\Minifier::minify($contents, $minify);
    }
}
