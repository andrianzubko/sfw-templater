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
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function transform(string $template, array|object|null $context = null): string
    {
        $timer = gettimeofday(true);

        if (!str_ends_with($template, '.xsl')) {
            $template .= '.xsl';
        }

        $template = $this->options['dir'] . '/' . $template;

        if (!isset($this->processors[$template])) {
            $doc = new \DOMDocument();

            if (!$doc->load($template, LIBXML_NOCDATA)) {
                throw new LogicException('XSL loading error');
            }

            $processor = new \XSLTProcessor();

            if (!$processor->importStylesheet($doc)) {
                throw new LogicException('XSL import error');
            }

            $this->processors[$template] = $processor;
        }

        $context = [...$this->options['globals'], ...(array) $context];

        $sxe = Util\ArrayToSXE::transform($context, $this->options['root'], $this->options['item']);

        $contents = $this->processors[$template]->transformToXML($sxe) ?? '';

        if ($contents === false) {
            throw new LogicException('XSL transform error');
        }

        self::$timer += gettimeofday(true) - $timer;

        self::$counter += 1;

        return $contents;
    }
}
