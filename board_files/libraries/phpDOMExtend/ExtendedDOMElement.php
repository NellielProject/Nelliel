<?php

namespace phpDOMExtend;

class ExtendedDOMElement extends \DOMElement
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

    public function doXPathQuery($expression, $context_node = null)
    {
        $xpath = new \DOMXPath($this->ownerDocument);
        return $xpath->query($expression, $context_node);
    }

    public function extSetAttribute($name, $value, $escape_type = 'attribute')
    {
        $this->doEscaping($value, $escape_type);
        $attribute = $this->ownerDocument->createAttribute($name);
        $attribute->value = $value;
        return $this->setAttributeNode($attribute);
    }

    public function extSetAttributeNS($namespaceURI, $qualifiedName, $value, $escape_type = 'attribute')
    {
        $this->doEscaping($value, $escape_type);
        $attribute = $this->ownerDocument->createAttributeNS($namespaceURI, $qualifiedName);
        $attribute->value = $value;
        return $this->setAttributeNodeNS($attribute);
    }

    public function modifyAttribute($name, $value, $relative = 'replace', $escape_type = 'attribute')
    {
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

        return $this->extSetAttribute($name, $value, $escape_type);
    }

    public function modifyAttributeNS($namespaceURI, $qualifiedName, $value, $relative = 'replace', $escape_type = 'attribute')
    {
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
        return $existing_value;
    }

    public function removeContent()
    {
        $old_value = $this->nodeValue;
        $this->nodeValue = null;
        return $old_value;
    }

    public function changeId($new_id)
    {
        $this->setAttribute('id', $new_id);
        $this->setIdAttribute('id', true);
    }

    public function getElementById($id)
    {
        return $this->doXPathQuery(".//*[@id='" . $id . "']", $this)->item(0);
    }

    public function getElementsByAttributeName($attribute_name)
    {
        return $this->doXPathQuery('.//*[@' . $attribute_name . ']', $this);
    }

    public function getElementsByAttributeValue($attribute, $attribute_name)
    {
        return $this->doXPathQuery('.//*[@' . $attribute . '=\'' . $attribute_name . '\']', $this);
    }

    public function getElementsByClassName($class_name, $context_node = null)
    {
        return $this->getElementsByAttributeValue('class', $class_name);
    }

    public function getInnerNode($as_list)
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
}