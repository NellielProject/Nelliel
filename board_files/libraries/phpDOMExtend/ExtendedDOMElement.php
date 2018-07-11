<?php

namespace phpDOMExtend;

use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMAttr;
use DOMXPath;

class ExtendedDOMElement extends DOMElement
{
    private $escaper_instance;

    function __construct($register = false)
    {
        parent::__construct();
        $this->escaper_instance = new DOMEscaper();
    }

    // Because PHP's DOM won't always call the subclass constructor
    private function doEscaping(&$content, $escape_type)
    {
        if (!isset($this->escaper_instance))
        {
            $this->escaper_instance = new DOMEscaper();
        }

        return $this->escaper_instance->doEscaping($content, $escape_type);
    }

    /**
     * Execute an XPath query on the current document and return the result.
     *
     * @param string $expression The XPath query
     * @param DOMNode [optional] $context_node Optional context node to limit the query scope
     * @return DOMNodeList Returns result of the query as a DOMNodeList object
     */
    public function doXPathQuery($expression, $context_node = null)
    {
        $xpath = new DOMXPath($this->ownerDocument);

        if(is_null($context_node))
        {
            $context_node = $this;
        }

        return $xpath->query($expression, $context_node);
    }

    /**
     * Extended setAttribute that adds escaping to the value.
     *
     * @param string $name Attribute name
     * @param string $value Attribute value
     * @param string $escape_type Type of escaping to use
     * @return DOMAttr The old node if replaced, otherwise null
     */
    public function extSetAttribute($name, $value, $escape_type = 'attribute')
    {
        $this->doEscaping($value, $escape_type);
        $attribute = $this->ownerDocument->createAttribute($name);
        $attribute->value = $value;
        return $this->setAttributeNode($attribute);
    }

    /**
     * Extended setAttributeNS that adds escaping to the value.
     * @param string $namespaceURI The URI of the namespace
     * @param string $qualifiedName The qualified name of the element
     * @param string $value Attribute value
     * @param string $escape_type Type of escaping to use
     * @return DOMAttr The old node if replaced, otherwise null
     */
    public function extSetAttributeNS($namespaceURI, $qualifiedName, $value, $escape_type = 'attribute')
    {
        $this->doEscaping($value, $escape_type);
        $attribute = $this->ownerDocument->createAttributeNS($namespaceURI, $qualifiedName);
        $attribute->value = $value;
        return $this->setAttributeNodeNS($attribute);
    }

    /**
     * Modify an existing attribute or add if the attribute does not exist yet.
     *
     * @param string $name Attribute name
     * @param string $value Attribute value
     * @param string $relative How to modify the existing value
     * @param string $escape_type Type of escaping to use
     * @return string The original attribute value
     */
    public function modifyAttribute($name, $value, $relative = 'replace', $escape_type = 'attribute')
    {
        $existing_content = '';

        if ($this->hasAttribute($name))
        {
            $existing_content = $this->getAttribute($name);

            if ($relative === 'after')
            {
                $value = $existing_content . $value;
            }
            else if ($relative === 'before')
            {
                $value = $value . $existing_content;
            }
        }

        $this->extSetAttribute($name, $value, $escape_type);
        return $existing_content;
    }

    /**
     * Modify an existing namespaced attribute or add if the attribute does not exist yet.
     *
     * @param string $namespaceURI The URI of the namespace
     * @param string $qualifiedName The qualified name of the element
     * @param string $value Attribute value
     * @param string $relative How to modify the existing value
     * @param string $escape_type Type of escaping to use
     * @return string The original attribute value
     */
    public function modifyAttributeNS($namespaceURI, $qualifiedName, $value, $relative = 'replace', $escape_type = 'attribute')
    {
        $existing_content = '';

        if ($this->hasAttributeNS($namespaceURI, $localName))
        {
            $existing_content = $this->getAttributeNodeNS($namespaceURI, $localName);

            if ($relative === 'after')
            {
                $value = $existing_content . $value;
            }
            else if ($relative === 'before')
            {
                $value = $value . $existing_contents;
            }
        }

        $this->extSetAttributeNS($namespaceURI, $qualifiedName, $value, $escape_type);
        return $existing_content;
    }

    /**
     * Gets the current node value.
     *
     * @return string The current node value
     */
    public function getContent()
    {
        return $this->nodeValue;
    }

    /**
     * Sets the current node value.
     *
     * @param string $value Node value
     * @param string $relative How to modify the existing value
     * @param string $escape_type Type of escaping to use
     * @return string The old node value
     */
    public function setContent($value, $relative = 'replace', $escape_type = 'html')
    {
        $this->doEscaping($value, $escape_type);
        $existing_value = $this->nodeValue;

        if ($relative === 'after')
        {
            $value = $existing_value . $value;
        }
        else if ($relative === 'before')
        {
            $value = $value . $existing_value;
        }

        $this->nodeValue = $value;
        return $existing_value;
    }

    /**
     * Remove the node value.
     *
     * @return string The old node value
     */
    public function removeContent()
    {
        $old_value = $this->nodeValue;
        $this->nodeValue = null;
        return $old_value;
    }

    /**
     * Change the node ID.
     *
     * @param string $new_id The new ID
     */
    public function changeId($new_id)
    {
        $this->setAttribute('id', $new_id);
        $this->setIdAttribute('id', true);
    }

    /**
     * Searches for nodes containing the given attribute and returns the nodes as a PHP associative array indexed
     * by attribute values.
     *
     * @param string $name Name of attribute to search for
     * @param DOMNode [optional] $context_node Optional context node to search within
     * @return array Associative array of nodes
     */
    public function getAssociativeNodeArray($name, $context_node = null)
    {
        $array = array();
        $node_list = $this->doXPathQuery(".//*[@" . $name . "]", $context_node);

        foreach($node_list as $node)
        {
            $array[$node->getAttribute($name)] = $node;
        }

        return $array;
    }

    /**
     * Get child element matching the given ID.
     *
     * @param string $id The ID to search for
     * @return DOMElement The first matching element
     */
    public function getElementById($id)
    {
        return $this->doXPathQuery("(.//*[@id='" . $id . "'])[1]", $this)->item(0);
    }

    /**
     * Get child elements which contain the given attribute name.
     *
     * @param string $name Name of the attribute
     * @return DOMNodeList A DOMNodeList of matching elements
     */
    public function getElementsByAttributeName($name)
    {
        return $this->doXPathQuery('.//*[@' . $name . ']', $this);
    }

    /**
     * Get child elements which contain the given attribute value.
     *
     * @param string $name Name of the attribute
     * @param string $value Attribute value to match
     * @return DOMNodeList A DOMNodeList of matching elements
     */
    public function getElementsByAttributeValue($name, $value)
    {
        return $this->doXPathQuery('.//*[@' . $name . '=\'' . $value . '\']', $this);
    }

    /**
     * Get child elements which contain the given class name.
     *
     * @param string $name Name of the class
     * @return DOMNodeList A DOMNodeList of matching elements
     */
    public function getElementsByClassName($name)
    {
        return $this->getElementsByAttributeValue('class', $name);
    }

    /**
     * Get the innder nodes of this element.
     *
     * @param boolean $as_list True to return nodes as a list or false to return a DOMDocument containing the nodes.
     * @return DOMNodeList|ExtendedDOMDocument
     */
    public function getInnerNode($as_list = false)
    {
        $nodes = $this->childNodes;

        if ($as_list)
        {
            return $nodes;
        }

        $inner_dom = new ExtendedDOMDocument();

        foreach ($nodes as $node)
        {
            $inner_dom->appendChild($inner_dom->importNode($node, true));
        }

        return $inner_dom;
    }

    /**
     * Delete this node.
     */
    public function removeSelf()
    {
        $parent = $this->parentNode;

        if (!is_null($parent))
        {
            $parent->removeChild($this);
        }
        else
        {
            $this->ownerDocument->removeChild($this);
        }
    }

    /**
     * Adds a new child after a reference node
     *
     * @param DOMNode $newnode The new node.
     * @param DOMNode $refnode The reference node. If not supplied, newnode is appended to the children.
     * @return DOMNode The inserted node.
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
     * @param DOMNode $node The node to be copied.
     * @param DOMNode $target_node The target node to insert the new copy.
     * @param string $insert Where to insert the copied node relative to the target.
     * @return DOMNode The copied node.
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
            return $this->insertAfter($node->cloneNode(true), $target_node);
        }
        else if($insert === 'append')
        {
            return $target_node->appendChild($node->cloneNode(true));
        }

        return $node;
    }
}