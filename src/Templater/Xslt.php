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
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function transform(string $filename, array|object|null $context = null): string
    {
        $timer = gettimeofday(true);

        $filename = $this->normalizeFilename($filename, 'xsl', $this->options['dir']);

        $context = $this->normalizeContext($context);

        if (!isset($this->processors[$filename])) {
            $doc = new \DOMDocument();

            if (!$doc->load($filename, LIBXML_NOCDATA)) {
                throw new LogicException('XSL loading error');
            }

            $processor = new \XSLTProcessor();

            if (!$processor->importStylesheet($doc)) {
                throw new LogicException('XSL import error');
            }

            $this->processors[$filename] = $processor;
        }

        $context = [...$this->options['globals'], ...$context];

        $sxe = Util\ArrayToSXE::transform($context, $this->options['root'], $this->options['item']);

        $contents = $this->processors[$filename]->transformToXML($sxe) ?? '';

        if ($contents === false) {
            throw new LogicException('XSL transform error');
        }

        self::$timer += gettimeofday(true) - $timer;

        self::$counter += 1;

        return $contents;
    }
}
