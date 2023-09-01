<?php

namespace SFW\Templater;

/**
 * Native templater.
 */
class Native extends Processor
{
    /**
     * Passing parameters to properties and initializing default properties.
     */
    public function __construct(protected array $options = [])
    {
        $this->properties['h'] = function (?string $string): string {
            return htmlspecialchars($string ?? '', ENT_COMPAT, 'UTF-8');
        };

        $this->properties['u'] = function (?string $string): string {
            return urlencode($string ?? '');
        };

        $this->properties['j'] = function (?string $string): string {
            return str_replace(
                ['\\','/',"\n","\r",' ','"',"'",'<','>','&',"\xe2\x80\xa8","\xe2\x80\xa9"],
                    ['\\\\','\\/','\\n','\\r','\\x20','\\x22','\\x27','\\x3C','\\x3E','\\x26','\\u2028','\\u2029'],
                        $string ?? ''
            );
        };
    }

    /**
     * Transforming template to page.
     *
     * @throws Exception
     */
    public function transform(array $e, string $template): string
    {
        $timer = gettimeofday(true);

        try {
            ob_start(fn() => null);

            $isolator = new Native\Isolator($this->options['dir'] . "/$template",
                array_merge($this->properties,
                    [
                        'e' => $e,
                    ]
                )
            );

            ob_clean();

            if (isset($isolator->main)
                && $isolator->main instanceof \Closure
            ) {
                $isolator->main();
            }

            $contents = ob_get_clean();
        } catch (\Exception | \Error $error) {
            ob_end_clean();

            throw new Exception(
                $error instanceof \Error
                    ? sprintf('PHP %s: %s', $error::class, $error->getMessage())
                        : $error->getMessage(),

                $error->getFile(),
                $error->getLine()
            );
        }

        if ($this->options['minify'] ?? true) {
            if ($this->options['debug'] ?? false) {
                $minifier = new Native\Debugger();
            } else {
                $minifier = new Native\Minifier();
            }

            $contents = $minifier->transform($contents);
        }

        self::$timer += gettimeofday(true) - $timer;

        self::$counter += 1;

        return rtrim($contents) . "\n";
    }
}