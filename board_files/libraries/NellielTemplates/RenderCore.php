<?php
namespace NellielTemplates;

class RenderCore
{
    private $version;
    private $template_instance;
    private $dom_documents;
    private $render_sets;

    function __construct()
    {
        $this->version = '1.0.2';
        $this->template_instance = new TemplateCore($this);
        libxml_use_internal_errors(true);
        $this->createRenderSet('default');
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function newDOMDocument()
    {
        $dom = new \phpDOMExtend\ExtendedDOMDocument();
        $this->dom_documents[spl_object_hash($dom)]['template'] = null;
        $dom->formatOutput = true;
        $dom->strictErrorChecking = false;
        $dom->validateOnParse = true;
        return $dom;
    }

    public function createRenderSet($render_set = 'default')
    {
        if(!isset($this->render_sets[$render_set]))
        {
            $this->render_sets[$render_set]['content'] = '';
        }
    }

    public function startRenderTimer($render_set = 'default')
    {
        $this->render_sets[$render_set]['start_time'] = microtime(true);
    }

    public function endRenderTimer($render_set = 'default')
    {
        $this->render_sets[$render_set]['end_time'] = microtime(true);
        return $this->render_sets[$render_set]['end_time'] - $this->render_sets[$render_set]['start_time'];
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
        $this->createRenderSet($render_set);
        $this->render_sets[$render_set]['content'] .= $html;
    }

    public function appendHTMLFromDOM($dom_document, $render_set = 'default')
    {
        $this->createRenderSet($render_set);
        $this->render_sets[$render_set]['content'] .= $this->outputHTML($dom_document, $this->dom_documents[spl_object_hash($dom_document)]['template']);
    }

    public function outputRenderSet($render_set = 'default')
    {
        return $this->render_sets[$render_set]['content'];
    }
}
