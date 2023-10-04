<?php

namespace SFW\Templater;

/**
 * Native templater.
 */
class Native extends Processor
{
    /**
     * Passes parameters to properties and checking some.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->options['minify'] ??= true;

        $this->options['debug'] ??= false;

        $this->options['properties'] ??= [];

        $this->options['properties']['h'] ??= function (?string $string): string {
            if (!isset($string)) {
                return '';
            }

            return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        };

        $this->options['properties']['u'] ??= function (?string $string): string {
            if (!isset($string)) {
                return '';
            }

            return urlencode($string);
        };

        $this->options['properties']['j'] ??= function (?string $string): string {
            if (!isset($string)) {
                return '';
            }

            return str_replace(' ', '\\x20',
                json_encode($string,
                    JSON_UNESCAPED_UNICODE | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG
                )
            );
        };
    }

    /**
     * Transforms template to page.
     *
     * @throws LogicException
     */
    public function transform(string $filename, array|object|null $context = null): string
    {
        $timer = gettimeofday(true);

        $filename = $this->options['dir'] . '/' . $this->normalizeFilename($filename, 'php');

        try {
            ob_start(fn() => null);

            $isolator = new NativeIsolator($filename, (array) $context, $this->options['properties']);

            ob_clean();

            if (isset($isolator->main)
                && $isolator->main instanceof \Closure
            ) {
                ($isolator->main)();
            }

            $contents = ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();

            throw (new LogicException($e->getMessage()))
                ->setFile($e->getFile())
                ->setLine($e->getLine());
        }

        if ($this->options['minify']
            && $this->mime === 'text/html'
        ) {
            $contents = $this->options['debug']
                ? Util\HTMLDebugger::transform($contents)
                : Util\HTMLMinifier::transform($contents);
        }

        self::$timer += gettimeofday(true) - $timer;

        self::$counter += 1;

        return $contents;
    }
}
