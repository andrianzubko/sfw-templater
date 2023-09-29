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
     * Passes parameters to properties and initializes default properties.
     */
    public function __construct(protected array $options = [])
    {
        $this->options['root'] ??= 'root';

        $this->options['item'] ??= 'item';

        $this->options['properties'] ??= [];
    }

    /**
     * Transforming template to page.
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function transform(array|object $context, string $template): string
    {
        $timer = gettimeofday(true);

        if (isset($this->options['dir'])
            && $template[0] !== '/'
        ) {
            $template = $this->options['dir'] . '/' . $template;
        }

        if (!isset($this->processors[$template])) {
            $doc = new \DOMDocument();

            if ($doc->load($template, LIBXML_NOCDATA) === false) {
                throw new LogicException('XSL loading error');
            }

            $processor = new \XSLTProcessor();

            if ($processor->importStylesheet($doc) === false) {
                throw new LogicException('XSL import error');
            }

            $processor->setParameter('', $this->options['properties']);

            $this->processors[$template] = $processor;
        }

        $sxe = Xslt\ArrayToSXE::transform(
            (array) $context, $this->options['root'], $this->options['item']
        );

        $contents = $this->processors[$template]->transformToXML($sxe) ?? '';

        if ($contents === false) {
            throw new LogicException('XSL transform error');
        }

        self::$timer += gettimeofday(true) - $timer;

        self::$counter += 1;

        return $contents;
    }
}
