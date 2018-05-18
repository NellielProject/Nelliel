<?php

namespace phpDOMExtend;

class ExtendedDOMDocument extends \DOMDocument
{
    private $escaper_instance;

    function __construct($register = false)
    {
        parent::__construct();

        $this->escaper_instance = new DOMEscaper();

        if ($register)
        {
            $this->registerNodeClasses();
        }
    }

    /**
     * This method will register ExtendedDOMDocument and/or ExtendedDOMElement as the classes for the DOM extension to
     * use.
     *
     * @param boolean $dom_document Register ExtendedDOMDocument
     * @param boolean $dom_element Register ExtendedDOMElement
     */
    public function registerNodeClasses($dom_document = true, $dom_element = true)
    {
        if ($dom_document)
        {
            $this->registerNodeClass('DOMDocument', 'phpDOMExtend\ExtendedDOMDocument');
        }

        if ($dom_element)
        {
            $this->registerNodeClass('DOMElement', 'phpDOMExtend\ExtendedDOMElement');
        }
    }

    /**
     * Execute an XPath query on the current document and return the result.
     *
     * @param string $expression The XPath query
     * @param DOMNode[optional] $context_node Optional context node to limit the query scope
     * @return DOMNodeList Result of the query as a DOMNodeList object
     */
    public function doXPathQuery($expression, $context_node = null)
    {
        $xpath = new \DOMXPath($this);
        return $xpath->query($expression, $context_node);
    }

    /**
     * Use the defined escaper to escape the passed content before output.
     *
     * @param string $content The content to escape
     * @param string $escape_type Type of escaping to use
     */
    public function doEscaping(&$content, $escape_type)
    {
        $this->escaper_instance->doEscaping($content, $escape_type);
    }

    /**
     * Extended createTextNode that adds escaping to the content.
     *
     * @param string $content TextNode content
     * @param string $escape_type Type of escaping to use
     * @return DOMText The new DOMText object or false if error ocurred
     */
    public function extCreateTextNode($content, $escape_type = 'html')
    {
        $this->doEscaping($content, $escape_type);
        return parent::createTextNode($content);
    }

    /**
     * Extended createElement that adds escaping to the value.
     *
     * @param string $name Tag name of the element
     * @param string[optional] $value The value of the element
     * @param string $escape_type Type of escaping to use
     * @return DOMElement The new DOMElement object or false if error ocurred
     */
    public function extCreateElement($name, $value = null, $escape_type = 'html')
    {
        if (!is_null($value))
        {
            $this->doEscaping($value, $escape_type);
        }

        return parent::createElement($name, $value);
    }

    /**
     * Extended createElementNS that adds escaping to the value.
     *
     * @param string $namespaceURI The URI of the namespace
     * @param string $qualifiedName The qualified name of the element, as prefix:tagname
     * @param string[optional] $value The value of the element
     * @param string $escape_type Type of escaping to use
     * @return DOMElement The new DOMElement object or false if error ocurred
     */
    public function extCreateElementNS($namespaceURI, $qualifiedName, $value = null, $escape_type = 'html')
    {
        if (!is_null($value))
        {
            $this->doEscaping($value, $escape_type);
        }

        return parent::createElementNS($namespaceURI, $qualifiedName, $value);
    }

    /**
     * Creates a complete DOM attribute node with a value.
     *
     * @param string $name Name of the attribute
     * @param string $value Attribute value
     * @param string $escape_type Type of escaping to use
     * @return DOMAttr The new DOMAttr object or false if error ocurred
     */
    public function createFullAttribute($name, $value, $escape_type = 'attribute')
    {
        $this->doEscaping($value, $escape_type);
        $attribute = $this->createAttribute($name);
        $attribute->value = $value;
        return $attribute;
    }

    /**
     * Creates a complete namespaced DOM attribute node with a value.
     *
     * @param string $namespaceURI The URI of the namespace
     * @param string $qualifiedName The qualified name of the element, as prefix:tagname
     * @param string $value Attribute value
     * @param string $escape_type Type of escaping to use
     * @return DOMAttr The new DOMAttr object or false if error ocurred
     */
    public function createFullAttributeNS($namespaceURI, $qualifiedName, $value, $escape_type = 'attribute')
    {
        $this->doEscaping($value, $escape_type);
        $attribute = $this->createAttributeNS($namespaceURI, $qualifiedName);
        $attribute->value = $value;
        return $attribute;
    }

    /**
     * Get elements which contain the given attribute name.
     *
     * @param string $name Name of the attribute
     * @param DOMNode[optional] $context_node Optional context node to search within
     * @return DOMNodeList A DOMNodeList of matching elements
     */
    public function getElementsByAttributeName($name, $context_node = null)
    {
        return $this->doXPathQuery('.//*[@' . $name . ']', $context_node);
    }

    /**
     * Get elements which contain the given attribute value.
     *
     * @param string $name Name of the attribute
     * @param string $value Attribute value to match
     * @param DOMNode[optional] $context_node Optional context node to search within
     * @return DOMNodeList A DOMNodeList of matching elements
     */
    public function getElementsByAttributeValue($name, $value, $context_node = null)
    {
        return $this->doXPathQuery('.//*[@' . $name . '=\'' . $value . '\']', $context_node);
    }

    /**
     * Get elements which contain the given class name.
     *
     * @param string $name Name of the class
     * @param DOMNode[optional] $context_node Optional context node to search within
     * @return DOMNodeList A DOMNodeList of matching elements
     */
    public function getElementsByClassName($name, $context_node = null)
    {
        return $this->getElementsByAttributeValue('class', $name, $context_node);
    }

    /**
     * Remove a parent node while keeping any child nodes in place.
     *
     * @param DOMNode $node
     */
    public function removeElementKeepChildren($node)
    {
        $children = $element->getInnerNode(true);
        $parent = $element->parentNode;

        foreach ($children as $child_node)
        {
            $parent->insertBefore($child_node->cloneNode(true), $element);
        }

        $element->removeSelf();
    }

    /**
     * Searches for nodes containing the given attribute and returns the nodes as a PHP associative array indexed
     * by attribute values.
     *
     * @param string $name Name of attribute to search for
     * @param DOMNode[optional] $context_node Optional context node to search within
     * @return array Associative array of nodes
     */
    public function getAssociativeNodeArray($name, $context_node = null)
    {
        $array = array();
        $node_list = $this->doXPathQuery(".//*[@" . $name . "]", $context_node);

        foreach ($node_list as $node)
        {
            $array[$node->getAttribute($name)] = $node;
        }

        return $array;
    }

    /**
     * Copies the given node into a new ExtendedDOMDocument
     *
     * @param DOMNode $node Node to be copied
     * @param boolean $deep Deep copy
     * @return ExtendedDOMDocument The new ExtendedDOMDocument containing the copied node
     */
    public function copyNodeIntoDocument($node, $deep = false)
    {
        $new_dom = new ExtendedDOMDocument(true);
        $new_dom->validateOnParse = true;
        $new_dom->loadHTML('<!DOCTYPE html>');
        $importNode = $new_dom->importNode($node, $deep);
        $new_dom->appendChild($importNode);
        return $new_dom;
    }
}