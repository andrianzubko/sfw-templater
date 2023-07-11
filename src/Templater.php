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
            'h' => function (?string $string): string {
                return htmlspecialchars($string ?? '', ENT_COMPAT, 'UTF-8');
            },
            'u' => function (?string $string): string {
                return urlencode($string ?? '');
            },
            'j' => function (?string $string): string {
                return str_replace(
                    ['\\','/',"\n","\r",' ','"',"'",'<','>','&',"\xe2\x80\xa8","\xe2\x80\xa9"],
                        ['\\\\','\\/','\\n','\\r','\\x20','\\x22','\\x27','\\x3C','\\x3E','\\x26','\\u2028','\\u2029'],
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
    public function transform(array $e, string $template, array $options = []): string
    {
        if (isset($options['timezone'])) {
            $tzPrev = date_default_timezone_get();

            if ($tzPrev === $options['timezone']) {
                $tzPrev = null;
            } else {
                date_default_timezone_set($options['timezone']);
            }
        } else {
            $tzPrev = null;
        }

        ob_start(fn() => null);

        $isolator = new Templater\Isolator($template,
            array_merge($this->properties,
                ['e' => $e]
            )
        );

        ob_clean();

        if (($isolator->main ?? null) instanceof \Closure) {
            $isolator->main();
        }

        $contents = ob_get_clean();

        if (isset($tzPrev)) {
            date_default_timezone_set($tzPrev);
        }

        if ($options['debug'] ?? false) {
            return (new Templater\Debugger())->transform($contents);
        }

        return (new Templater\Minifier())->transform($contents);
    }
}
