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

    public function registerNodeClasses($dom_document = true, $dom_element = true)
    {
        if($dom_document)
        {
            $this->registerNodeClass('DOMDocument', 'phpDOMExtend\ExtendedDOMDocument');
        }

        if($dom_element)
        {
            $this->registerNodeClass('DOMElement', 'phpDOMExtend\ExtendedDOMElement');
        }
    }

    public function doXPathQuery($expression, $context_node = null)
    {
        $xpath = new \DOMXPath($this);
        return $xpath->query($expression, $context_node);
    }

    public function doEscaping(&$content, $escape_type)
    {
        $this->escaper_instance->doEscaping($content, $escape_type);
    }

    public function extCreateTextNode($content, $escape_type = 'html')
    {
        $this->doEscaping($content, $escape_type);
        return parent::createTextNode($content);
    }

    public function extCreateElement($name, $value = null, $escape_type = 'html')
    {
        if (!is_null($value))
        {
            $this->doEscaping($value, $escape_type);
        }

        return parent::createElement($name, $value);
    }

    public function extCreateElementNS($namespaceURI, $qualifiedName, $value = null, $escape_type = 'html')
    {
        if (!is_null($value))
        {
            $this->doEscaping($value, $escape_type);
        }

        return parent::createElementNS($namespaceURI, $qualifiedName, $value);
    }

    public function createFullAttribute($name, $content, $escape_type = 'attribute')
    {
        $this->doEscaping($content, $escape_type);
        $attribute = $this->createAttribute($name);
        $attribute->value = $content;
        return $attribute;
    }

    public function createFullAttributeNS($namespaceURI, $qualifiedName, $content, $escape_type = 'attribute')
    {
        $this->doEscaping($content, $escape_type);
        $attribute = $this->createAttributeNS($namespaceURI, $qualifiedName);
        $attribute->value = $content;
        return $attribute;
    }

    public function getElementsByAttributeName($attribute_name, $context_node = null)
    {
        return $this->doXPathQuery('.//*[@' . $attribute_name . ']', $context_node);
    }

    public function getElementsByAttributeValue($attribute, $attribute_name, $context_node = null)
    {
        return $this->doXPathQuery('.//*[@' . $attribute . '=\'' . $attribute_name . '\']', $context_node);
    }

    public function getElementsByClassName($class_name, $context_node = null)
    {
        return $this->getElementsByAttributeValue('class', $class_name, $context_node);
    }

    public function removeElementKeepChildren($element)
    {
        $children = $element->getInnerNode(true);
        $parent = $element->parentNode;

        foreach ($children as $child_node)
        {
            $parent->insertBefore($child_node->cloneNode(true), $element);
        }

        $element->removeSelf();
    }

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