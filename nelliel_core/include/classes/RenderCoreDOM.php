<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class RenderCoreDOM extends RenderCore
{
    private $template_instance;
    private $dom_documents;

    function __construct()
    {
        $this->template_instance = new \NellielTemplates\TemplateCore($this);
        $this->template_loaders['file'] = $this->template_instance;
        $this->template_loaders['string'] = $this->template_instance;
        libxml_use_internal_errors(true);
        $this->output_filter = new \Nelliel\OutputFilter();
        $this->file_handler = new \Nelliel\Utility\FileHandler();
    }

    public function newDOMDocument()
    {
        $dom = new \phpDOMExtend\ExtendedDOMDocument();
        $this->dom_documents[spl_object_hash($dom)]['template'] = null;
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = true;
        $dom->strictErrorChecking = false;
        $dom->validateOnParse = true;
        return $dom;
    }

    public function templatePath($new_path = null)
    {
        if(!is_null($new_path))
        {
            $this->getTemplateInstance()->templatePath($new_path);
        }

        return $this->getTemplateInstance()->templatePath();
    }

    public function getTemplateInstance()
    {
        return $this->template_instance;
    }

    public function loadTemplateFromFile(string $file)
    {
        $this->template_loaders['file']->loadTemplateFromFile($file);
        return $this->template_instance->getTemplate($file, true);
    }

    public function loadTemplateFromString(string $template, string $contents)
    {

        $this->template_loaders['string']->loadTemplateFromString($template, $contents);
        return $this->template_instance->getTemplate($template, true);
    }

    public function loadDOMFromTemplate($dom_document, string $template)
    {
        $dom_document->loadHTML($template, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    }

    public function renderFromTemplateFile(string $file, array $render_data)
    {
        return $this->template_instance->outputHTMLFromDom($render_data['dom_document'], $file);
    }

    public function renderFromDOM($dom_document, string $template_id)
    {
        return $this->template_instance->outputHTMLFromDom($dom_document, $template_id);
    }

    public function appendHTMLFromDOM($dom_document, string $template_id, $output_id = 'default')
    {
        $this->createOutput($output_id);
        $this->output_sets[$output_id]['content'] .= $this->renderFromDOM($dom_document, $template_id);
    }

    public function prepareForRender($template_file)
    {
        $new_dom = $this->newDOMDocument();
        $this->dom_documents[spl_object_hash($new_dom)] = $new_dom;
        $template = $render->loadTemplateFromFile($template_file);
        $render->loadDOMFromTemplate($new_dom, $template);
    }
}
