<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace SFW\Templater;

/**
 * Xslt templater.
 */
class Xslt extends Processor
{
    /**
     * Xslt processor instances.
     */
    protected array $processors;

    /**
     * Passes parameters to properties and checking some.
     *
     * @throws Exception\InvalidArgument
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->options['root'] ??= 'root';

        $this->options['item'] ??= 'item';

        $this->options['globals'] ??= [];
    }

    /**
     * Transforming template to page.
     *
     * If context is an object, then only public non-static properties will be taken.
     *
     * @throws Exception\InvalidArgument
     * @throws Exception\Logic
     */
    public function transform(string $filename, array|object|null $context = null): string
    {
        $timer = gettimeofday(true);

        $filename = $this->normalizeFilename($filename, 'xsl', $this->options['dir']);

        $context = [...$this->options['globals'], ...$this->normalizeContext($context)];

        if (!isset($this->processors[$filename])) {
            $doc = new \DOMDocument();

            if (!$doc->load($filename, LIBXML_NOCDATA)) {
                throw new Exception\Logic('XSL loading error');
            }

            $processor = new \XSLTProcessor();

            if (!$processor->importStylesheet($doc)) {
                throw new Exception\Logic('XSL import error');
            }

            $this->processors[$filename] = $processor;
        }

        $sxe = Utility\ArrayToSXE::transform($context, $this->options['root'], $this->options['item']);

        $contents = $this->processors[$filename]->transformToXML($sxe) ?? '';

        if ($contents === false) {
            throw new Exception\Logic('XSL transform error');
        }

        self::$timer += gettimeofday(true) - $timer;

        self::$counter += 1;

        return $contents;
    }
}
