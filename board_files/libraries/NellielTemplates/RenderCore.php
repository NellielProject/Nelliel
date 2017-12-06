<?php
namespace NellielTemplates;

class RenderCore
{
    private $template_instance;

    function __construct()
    {
        $this->template_instance = new TemplateCore($this);
        libxml_use_internal_errors(true);
    }

    public function newDOMDocument()
    {
        $dom = new NellielDOMDocument($this);
        return $dom;
    }

    public function getTemplateInstance()
    {
        return $this->template_instance;
    }
}
