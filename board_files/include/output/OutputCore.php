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
    protected $render_instance;
    protected $file_handler;
    protected $output_filter;

    public abstract function render(array $parameters = array());

    protected function prepare(string $template_file)
    {
        $this->render_instance = $this->domain->renderInstance();
        $this->dom = $this->render_instance->newDOMDocument();
        $this->render_instance->loadTemplateFromFile($this->dom, $template_file);
        $this->render_instance->startRenderTimer();
    }
}