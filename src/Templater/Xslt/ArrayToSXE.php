<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace SFW\Templater\Xslt;

use SFW\Templater\InvalidArgumentException;

/**
 * Transformer.
 */
class ArrayToSXE
{
    /**
     * Transforming array to SimpleXMLElement.
     *
     * @throws InvalidArgumentException
     */
    public static function transform(
        array $array,
        string $root = 'root',
        string $item = 'item',
        ?\SimpleXMLElement $sxe = null
    ): \SimpleXMLElement {
        if (!isset($sxe)) {
            $dom = new \DOMDocument('1.0', 'utf-8');

            try {
                $dom->appendChild(
                    new \DOMElement($root)
                );
            } catch (\DOMException $e) {
                throw new InvalidArgumentException($e->getMessage());
            }

            $sxe = simplexml_import_dom($dom);
        }

        foreach ($array as $key => $element) {
            if (is_int($key)) {
                if (is_array($element)) {
                    self::transform($element, $root, $item, $sxe->addChild($item));
                } elseif (
                    $element instanceof \SimpleXMLElement
                ) {
                    $node = dom_import_simplexml($sxe->addChild($item));

                    foreach (dom_import_simplexml($element)->childNodes as $child) {
                        $node->appendChild(
                            $node->ownerDocument->importNode($child, true)
                        );
                    }
                } else {
                    $sxe->{$item}[] = $element;
                }
            } else {
                if ($key[0] === '@') {
                    $sxe[substr($key, 1)] = $element;
                } elseif (
                    is_array($element)
                ) {
                    self::transform($element, $root, $item, $sxe->addChild($key));
                } elseif (
                    $element instanceof \SimpleXMLElement
                ) {
                    $node = dom_import_simplexml($sxe->addChild($key));

                    foreach (dom_import_simplexml($element)->childNodes as $child) {
                        $node->appendChild(
                            $node->ownerDocument->importNode($child, true)
                        );
                    }
                } else {
                    $sxe->{$key} = $element;
                }
            }
        }

        return $sxe;
    }
}
