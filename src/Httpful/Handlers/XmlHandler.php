<?php declare(strict_types=1);
/**
 * Mime Type: application/xml
 *
 * @author Zack Douglas <zack@zackerydouglas.info>
 * @author Nathan Good <me@nategood.com>
 */

namespace Httpful\Handlers;

use DOMDocument;
use DOMException;
use Exception;
use ReflectionObject;
use SimpleXMLElement;
use XMLWriter;

class XmlHandler extends MimeHandlerAdapter
{
    /**
     *  @var string $namespace xml namespace to use with simple_load_string
     */
    private string $namespace;

    /**
     * @var int $libxml_opts see http://www.php.net/manual/en/libxml.constants.php
     */
    private int $libxml_opts;

    /**
     * @param array $conf sets configuration options
     */
    public function __construct(array $conf = array())
    {
        parent::__construct($conf);
        $this->namespace = $conf['namespace'] ?? '';
        $this->libxml_opts = $conf['libxml_opts'] ?? 0;
    }

    /**
     * @param string $body
     * @return SimpleXMLElement|null
     * @throws Exception if unable to parse
     */
    public function parse(string $body): SimpleXMLElement|null
    {
        $body = $this->stripBom($body);
        if (empty($body))
            return null;
        $parsed = simplexml_load_string($body, null, $this->libxml_opts, $this->namespace);
        if ($parsed === false)
            throw new Exception("Unable to parse response as XML");
        return $parsed;
    }

    /**
     * @param mixed $payload
     * @return string
     * @throws Exception if unable to serialize
     */
    public function serialize(mixed $payload): string
    {
        list(, $dom) = $this->futureSerializeAsXml($payload);
        return $dom->saveXml();
    }

    /**
     * @param mixed $payload
     * @return string
     * @author Ted Zellers
     */
    public function serialize_clean(mixed $payload): string
    {
        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0','ISO-8859-1');
        $this->serialize_node($xml, $payload);
        return $xml->outputMemory();
    }

    /**
     * @param XMLWriter $xmlw
     * @param mixed $node to serialize
     * @author Ted Zellers
     */
    public function serialize_node(XMLWriter &$xmlw, mixed $node): void
    {
        if (!is_array($node)){
            $xmlw->text($node);
        } else {
            foreach ($node as $k => $v){
                $xmlw->startElement($k);
                    $this->serialize_node($xmlw, $v);
                $xmlw->endElement();
            }
        }
    }

    /**
     * @author Zack Douglas <zack@zackerydouglas.info>
     * @throws DOMException
     */
    private function futureSerializeAsXml($value, $node = null, $dom = null): array
    {
        if (!$dom) {
            $dom = new DOMDocument;
        }
        if (!$node) {
            if (!is_object($value)) {
                $node = $dom->createElement('response');
                $dom->appendChild($node);
            } else {
                $node = $dom;
            }
        }
        if (is_object($value)) {
            $objNode = $dom->createElement(get_class($value));
            $node->appendChild($objNode);
            $this->futureSerializeObjectAsXml($value, $objNode, $dom);
        } else if (is_array($value)) {
            $arrNode = $dom->createElement('array');
            $node->appendChild($arrNode);
            $this->futureSerializeArrayAsXml($value, $arrNode, $dom);
        } else if (is_bool($value)) {
            $node->appendChild($dom->createTextNode($value?'TRUE':'FALSE'));
        } else {
            $node->appendChild($dom->createTextNode($value));
        }
        return array($node, $dom);
    }
    /**
     * @author Zack Douglas <zack@zackerydouglas.info>
     * @throws DOMException
     */
    private function futureSerializeArrayAsXml($value, $parent, DOMDocument $dom): void
    {
        foreach ($value as $k => $v) {
            $n = $k;
            if (is_numeric($k)) {
                $n = "child-$n";
            }
            $el = $dom->createElement($n);
            $parent->appendChild($el);
            $this->futureSerializeAsXml($v, $el, $dom);
        }
    }
    /**
     * @author Zack Douglas <zack@zackerydouglas.info>
     * @throws DOMException
     */
    private function futureSerializeObjectAsXml($value, $parent, DOMDocument $dom): void
    {
        $refl = new ReflectionObject($value);
        foreach ($refl->getProperties() as $pr) {
            if (!$pr->isPrivate()) {
                $el = $dom->createElement($pr->getName());
                $parent->appendChild($el);
                $this->futureSerializeAsXml($pr->getValue($value), $el, $dom);
            }
        }
    }
}