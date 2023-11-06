<?php
declare(strict_types=1);

namespace SFW\Templater;

/**
 * Native templater.
 */
class Native extends Processor
{
    /**
     * Passes parameters to properties and checking some.
     *
     * @throws Exception\InvalidArgument
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->options['minify'] ??= true;

        $this->options['debug'] ??= false;

        $this->options['properties'] ??= [];

        $this->options['properties']['h'] ??= function (?string $string): string {
            if ($string === null) {
                return '';
            }

            return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        };

        $this->options['properties']['u'] ??= function (?string $string): string {
            if ($string === null) {
                return '';
            }

            return urlencode($string);
        };

        $this->options['properties']['j'] ??= function (?string $string): string {
            if ($string === null) {
                return '""';
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
     * If context is an object, then only public non-static properties will be taken.
     *
     * @throws Exception\Logic
     */
    public function transform(string $filename, array|object|null $context = null): string
    {
        $timer = gettimeofday(true);

        $filename = $this->normalizeFilename($filename, 'php', $this->options['dir']);

        $context = $this->normalizeContext($context);

        try {
            ob_start(fn() => null);

            $isolator = new NativeIsolator($filename, $context, $this->options['properties']);

            ob_clean();

            if (isset($isolator->main) && $isolator->main instanceof \Closure) {
                ($isolator->main)();
            }

            $contents = ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();

            throw (new Exception\Logic($e->getMessage()))
                ->setFile($e->getFile())
                ->setLine($e->getLine());
        }

        if ($this->options['minify'] && $this->mime === 'text/html') {
            if ($this->options['debug']) {
                $contents = Utility\HTMLDebugger::transform($contents);
            } else {
                $contents = Utility\HTMLMinifier::transform($contents);
            }
        }

        self::$timer += gettimeofday(true) - $timer;

        self::$counter += 1;

        return $contents;
    }
}
