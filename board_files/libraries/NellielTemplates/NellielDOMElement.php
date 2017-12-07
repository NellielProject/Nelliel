<?php

namespace NellielTemplates;

class NellielDOMElement extends \DOMElement
{
    private $escaper_instance;

    function __construct()
    {
        parent::__construct();
    }

    // Because PHP's DOM for some reason won't always call the subclass constructor
    private function doEscaping(&$content, $escape_type)
    {
        if(!isset($this->escaper_instance))
        {
            $this->escaper_instance = new NellielEscaper();
        }

        return $this->escaper_instance->doEscaping($content, $escape_type);
    }

    public function extSetAttribute($name, $value, $escape_type = 'attribute')
    {
        $attribute = $this->ownerDocument->createFullAttribute($name, $value, $escape_type);
        return $this->setAttributeNode($attribute);
    }

    public function extSetAttributeNS($namespaceURI, $qualifiedName, $value, $escape_type = 'attribute')
    {
        $attribute = $this->ownerDocument->createFullAttributeNS($namespaceURI, $qualifiedName, $value, $escape_type);
        return $this->setAttributeNodeNS($attribute);
    }

    public function modifyAttribute($name, $value, $relative = 'replace', $spacer = '', $escape_type = 'attribute')
    {
        if ($this->hasAttribute($name))
        {
            $existing_content = $this->getAttribute($name);

            if ($relative === 'after')
            {
                $value = $existing_content . $spacer . $value;
            }
            else if ($relative === 'before')
            {
                $value = $value . $spacer . $existing_contents;
            }
        }

        return $this->extSetAttribute($name, $value, $escape_type);
    }

    public function modifyAttributeNS($namespaceURI, $qualifiedName, $value, $relative = 'replace', $spacer = '',
            $escape_type = 'attribute')
    {
        if ($this->hasAttributeNS($namespaceURI, $localName))
        {
            $existing_content = $this->getAttributeNodeNS($namespaceURI, $localName);

            if ($relative === 'after')
            {
                $value = $existing_content . $spacer . $value;
            }
            else if ($relative === 'before')
            {
                $value = $value . $spacer . $existing_contents;
            }
        }

        return $this->extSetAttributeNS($namespaceURI, $qualifiedName, $value, $escape_type);
    }

    public function getContent()
    {
        return $this->nodeValue;
    }

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
    }

    public function removeContent()
    {
        $old_value = $this->nodeValue;
        $this->nodeValue = null;
        return $old_value;
    }

    public function getInnerNode()
    {
        $nodes = $this->childNodes;
        $inner_dom = new \DOMDocument();

        foreach ($nodes as $node)
        {
            $inner_dom->appendChild($inner_dom->importNode($node, true));
        }

        return $inner_dom;
    }
}