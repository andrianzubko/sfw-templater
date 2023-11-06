<?php /** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace SFW\Templater\Utility;

use \SFW\Templater\Exception;

/**
 * Transformer.
 */
class ArrayToSXE
{
    /**
     * Transforms array to SimpleXMLElement.
     *
     * @throws Exception\InvalidArgument
     */
    public static function transform(array $array, string $root = 'root', string $item = 'item'): \SimpleXMLElement
    {
        $dom = new \DOMDocument('1.0', 'utf-8');

        try {
            $dom->appendChild(new \DOMElement($root));
        } catch (\DOMException $e) {
            throw new Exception\InvalidArgument($e->getMessage());
        }

        return static::transformRecursive($array, $item, simplexml_import_dom($dom));
    }

    /**
     * Base method for transform.
     */
    protected static function transformRecursive(array $array, string $item, \SimpleXMLElement $sxe): \SimpleXMLElement
    {
        foreach ($array as $key => $value) {
            if (\is_int($key)) {
                if (\is_scalar($value)) {
                    $sxe->{$item}[] = $value;
                } elseif (\is_array($value)) {
                    static::transformRecursive($value, $item, $sxe->addChild($item));
                } elseif (\is_object($value)) {
                    if ($value instanceof \SimpleXMLElement) {
                        $node = dom_import_simplexml($sxe->addChild($item));

                        foreach (dom_import_simplexml($value)->childNodes as $child) {
                            $node->appendChild($node->ownerDocument->importNode($child, true));
                        }
                    } else {
                        static::transformRecursive((array) $value, $item, $sxe->addChild($item));
                    }
                }
            } else {
                if (str_starts_with($key, '@')) {
                    $sxe[substr($key, 1)] = $value;
                } elseif (\is_scalar($value)) {
                    $sxe->{$key} = $value;
                } elseif (\is_array($value)) {
                    static::transformRecursive($value, $item, $sxe->addChild($key));
                } elseif (\is_object($value)) {
                    if ($value instanceof \SimpleXMLElement) {
                        $node = dom_import_simplexml($sxe->addChild($key));

                        foreach (dom_import_simplexml($value)->childNodes as $child) {
                            $node->appendChild($node->ownerDocument->importNode($child, true));
                        }
                    } else {
                        static::transformRecursive((array) $value, $item, $sxe->addChild($key));
                    }
                }
            }
        }

        return $sxe;
    }
}
