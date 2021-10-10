<?php

namespace phpDOMExtend;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMAttr;
use DOMText;

class ExtendedDOMDocument extends DOMDocument
{
    function __construct()
    {
        parent::__construct();
        $this->registerNodeClass('DOMElement', 'phpDOMExtend\ExtendedDOMElement');
    }

    /**
     * Execute an XPath query on this document and return the result.
     *
     * @param string $expression The XPath query
     * @param DOMNode [optional] $context_node Optional context node to limit the query scope
     * @return DOMNodeList Result of the query as a DOMNodeList object
     */
    public function doXPathQuery($expression, $context_node = null)
    {
        return DOMHelperFunctions::doXPathQuery($this, $expression, $context_node);
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
        DOMEscaper::doEscaping($content, $escape_type);
        return parent::createTextNode($content);
    }

    /**
     * Extended createElement that adds escaping to the value.
     *
     * @param string $name Tag name of the element
     * @param string [optional] $value The value of the element
     * @param string $escape_type Type of escaping to use
     * @return DOMElement The new DOMElement object or false if error ocurred
     */
    public function extCreateElement($name, $value = null, $escape_type = 'html')
    {
        if (!is_null($value))
        {
            DOMEscaper::doEscaping($value, $escape_type);
        }

        return parent::createElement($name, $value);
    }

    /**
     * Extended createElementNS that adds escaping to the value.
     *
     * @param string $namespaceURI The URI of the namespace
     * @param string $qualifiedName The qualified name of the element, as prefix:tagname
     * @param string [optional] $value The value of the element
     * @param string $escape_type Type of escaping to use
     * @return DOMElement The new DOMElement object or false if error ocurred
     */
    public function extCreateElementNS($namespaceURI, $qualifiedName, $value = null, $escape_type = 'html')
    {
        if (!is_null($value))
        {
            DOMEscaper::doEscaping($value, $escape_type);
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
        DOMEscaper::doEscaping($value, $escape_type);
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
        DOMEscaper::doEscaping($value, $escape_type);
        $attribute = $this->createAttributeNS($namespaceURI, $qualifiedName);
        $attribute->value = $value;
        return $attribute;
    }

    /**
     * Get elements which contain the given attribute name.
     *
     * @param string $name Name of the attribute
     * @param DOMNode [optional] $context_node Optional context node to search within
     * @return DOMNodeList A DOMNodeList of matching elements
     */
    public function getElementsByAttributeName($name, $as_array = false)
    {
        $query_result = DOMHelperFunctions::doXPathQuery($this, './/*[@' . $name . ']');

        if($as_array)
        {
            return DOMHelperFunctions::attributeListToArray($query_result, $name);
        }

        return $query_result;
    }

    /**
     * Get elements which contain the given attribute value.
     *
     * @param string $name Name of the attribute
     * @param string $value Attribute value to match
     * @param DOMNode [optional] $context_node Optional context node to search within
     * @return DOMNodeList A DOMNodeList of matching elements
     */
    public function getElementsByAttributeValue($name, $value)
    {
        return DOMHelperFunctions::doXPathQuery($this, './/*[@' . $name . '=\'' . $value . '\']');
    }

    /**
     * Get elements which contain the given class name.
     *
     * @param string $name Name of the class
     * @param DOMNode [optional] $context_node Optional context node to search within
     * @return DOMNodeList A DOMNodeList of matching elements
     */
    public function getElementsByClassName($name)
    {
        return DOMHelperFunctions::doXPathQuery($this, './/*[@class=\'' . $name . '\']');
    }

    /**
     * Remove a parent node while keeping any child nodes in place.
     *
     * @param DOMNode $node
     */
    public function removeParentNode($node)
    {
        $children = $node->getInnerNode(true);
        $parent = $node->parentNode;

        foreach ($children as $child_node)
        {
            $parent->insertBefore($child_node->cloneNode(true), $node);
        }

        $node->removeSelf();
    }

    /**
     * Adds a new child after a reference node
     *
     * @param DOMNode $newnode The new node
     * @param DOMNode $refnode The reference node. If not supplied, newnode is appended to the children
     * @return DOMNode The inserted node
     */

    public function insertAfter($newnode, $refnode = null)
    {
        if(is_null($refnode))
        {
            return $this->appendChild($newnode);
        }

        $parent = $refnode->parentNode;
        $next = $refnode->nextSibling;

        if(!is_null($next))
        {
            return $parent->insertBefore($newnode, $next);
        }
        else
        {
            return $parent->appendChild($newnode);
        }

        return $newnode;
    }

    /**
     * Copies a node and inserts it relative to a target node
     *
     * @param DOMNode $node The node to be copied
     * @param DOMNode $target_node The target node to insert the new copy
     * @param string $insert Where to insert the copied node relative to the target
     * @return DOMNode The copied node
     */
    public function copyNode($node, $target_node, $insert)
    {
        $parent = $target_node->parentNode;

        if ($insert === 'before')
        {
            return $parent->insertBefore($node->cloneNode(true), $target_node);
        }
        else if($insert === 'after')
        {
            return self::insertAfter($node->cloneNode(true), $target_node);
        }
        else if($insert === 'append')
        {
            return $target_node->appendChild($node->cloneNode(true));
        }

        return $node;
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