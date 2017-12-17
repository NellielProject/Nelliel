<?php
namespace NellielTemplates;

class RenderCore
{
    private $template_instance;
    private $dom_documents;
    private $version;
    private $render_sets;

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
        $this->dom_documents[spl_object_hash($dom)]['template'] = null;
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

    public function appendHTML($html, $render_set = 'default')
    {
        if(!isset($this->render_sets[$render_set]))
        {
            $this->render_sets[$render_set] = '';
        }

        $this->render_sets[$render_set] .= $html;
    }

    public function appendHTMLFromDOM($dom_document, $render_set = 'default')
    {
        if(!isset($this->render_sets[$render_set]))
        {
            $this->render_sets[$render_set] = '';
        }

        $this->render_sets[$render_set] .= $this->outputHTML($dom_document, $this->dom_documents[spl_object_hash($dom_document)]['template']);
    }

    public function outputRenderSet($render_set = 'default')
    {
        return $this->render_sets[$render_set];
    }
}
