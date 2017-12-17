<?php
namespace NellielTemplates;

class RenderCore
{
    private $template_instance;
    private $dom_documents;
    private $version;

    function __construct()
    {
        $this->version = '1.0';
        $this->template_instance = new TemplateCore($this);
        libxml_use_internal_errors(true);
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function newDOMDocument()
    {
        $dom = new \phpDOMExtend\ExtendedDOMDocument(true);
        $dom->formatOutput = true;
        $dom->strictErrorChecking = false;
        $dom->validateOnParse = true;
        return $dom;
    }

    public function getTemplateInstance()
    {
        return $this->template_instance;
    }

    public function loadTemplateFromFile($dom_document, $template_file)
    {
        $this->dom_documents[spl_object_hash($dom_document)]['template'] = $template_file;
        $source = $this->template_instance->getTemplate($template_file);
        $dom_document->loadHTML($source);
    }

    public function outputHTML($dom_document)
    {
        return $this->template_instance->outputHTMLFromDom($dom_document, $this->dom_documents[spl_object_hash($dom_document)]['template']);
    }
}
