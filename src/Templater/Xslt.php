<?php

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
     * Passing parameters to properties.
     */
    public function __construct(protected array $options = []) {}

    /**
     * Transforming template to page.
     *
     * @throws InvalidArgumentException|RuntimeException
     */
    public function transform(array $e, string $template): string
    {
        $timer = gettimeofday(true);

        if (!isset($this->processors[$template])) {
            $doc = new \DOMDocument;

            if ($doc->load($this->options['dir'] . "/$template", LIBXML_NOCDATA) === false) {
                throw new RuntimeException('XSL loading error');
            }

            $processor = new \XSLTProcessor;

            if ($processor->importStylesheet($doc) === false) {
                throw new RuntimeException('XSL import error');
            }

            $this->processors[$template] = $processor;
        }

        if ($this->properties) {
            $this->processors[$template]->setParameter('', $this->properties);
        }

        $sxe = Xslt\ArrayToSXE::transform($e,
            $this->options['root'] ?? 'root',
            $this->options['item'] ?? 'item'
        );

        $contents = $this->processors[$template]->transformToXML($sxe) ?? '';

        if ($contents === false) {
            throw new RuntimeException('XSL transform error');
        }

        self::$timer += gettimeofday(true) - $timer;

        self::$counter += 1;

        return $contents;
    }
}
