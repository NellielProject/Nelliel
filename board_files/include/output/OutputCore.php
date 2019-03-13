<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class OutputCore
{
    protected $domain;
    protected $dom;
    protected $database;
    protected $render_instance;
    protected $file_handler;
    protected $output_filter;
    protected $header_instance;

    public abstract function render(array $parameters = array());

    protected function setupHeaderFooter()
    {
        $this->header_instance = new \Nelliel\Output\OutputHeader($this->domain, $this->database);
    }

    protected function prepare(string $template_file)
    {
        $this->render_instance = $this->domain->renderInstance();
        $this->dom = $this->render_instance->newDOMDocument();
        $this->render_instance->loadTemplateFromFile($this->dom, $template_file);
        $this->render_instance->startRenderTimer();
    }
}