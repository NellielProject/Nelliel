<?php

namespace NellielTemplates;

class NellielDOMDocument extends \DOMDocument
{
    private $escaper_instance;
    private $render_instance;
    private $template_instance;
    private $template;
    private $xpath;

    function __construct($render_instance = null)
    {
        parent::__construct();
        $this->render_instance = $render_instance;

        if(!is_null($render_instance))
        {
            $this->template_instance = $render_instance->getTemplateInstance();
        }

        $this->escaper_instance = new NellielEscaper();
        $this->registerNodeClass('DOMDocument', 'NellielTemplates\NellielDOMDocument');
        $this->registerNodeClass('DOMElement', 'NellielTemplates\NellielDOMElement');
        $this->formatOutput = true;
        $this->strictErrorChecking = false;
        $this->validateOnParse = true;
    }

    public function loadTemplateFromFile($template_file)
    {
        $this->template = $template_file;
        $source = $this->template_instance->getTemplate($template_file);
        $this->loadHTML($source);
        $this->xpath = new \DOMXPath($this);
    }

    public function outputHTML()
    {
        return $this->template_instance->outputHTMLFromDom($this, $this->template);
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
        if(!is_null($value))
        {
            $this->doEscaping($value, $escape_type);
        }

        return parent::createElement($name, $value);
    }

    public function extCreateElementNS($namespaceURI, $qualifiedName, $value = null, $escape_type = 'html')
    {
        if(!is_null($value))
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

    public function getXPath()
    {
        return $this->xpath;
    }

    public function doXPathQuery($expression, $context_node = null)
    {
        return $this->xpath->query($expression, $context_node);
    }

    public function getElementsByClassName($class_name, $context_node = null)
    {
        $this->getElementsByAttributeName($class_name, $context_node);
    }

    public function getElementsByAttributeName($attribute_name, $context_node = null)
    {
        return $this->xpath->query('//*[@' . $attribute_name . ']', $context_node);
    }
}