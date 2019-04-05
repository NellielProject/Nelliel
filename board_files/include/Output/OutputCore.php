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
    protected $render_core;
    protected $file_handler;
    protected $cache_handler;
    protected $output_filter;
    protected $url_constructor;

    public abstract function render(array $parameters = array());

    protected function utilitySetup()
    {
        $this->file_handler = new \Nelliel\FileHandler();
        $this->cache_handler = new \Nelliel\CacheHandler();
        $this->output_filter = new \Nelliel\OutputFilter();
        $this->url_constructor = new \Nelliel\URLConstructor();
    }

    protected function prepare(string $template_file)
    {
        $this->render_core = $this->domain->renderInstance();
        $this->dom = $this->render_core->newDOMDocument();
        $template = $render->loadTemplateFromFile($template_file);
        $render->loadDOMFromTemplate($this->dom, $template);
        $this->render_core->startTimer();
    }

    protected function selectRenderCore(string $core_id)
    {
        if($core_id === 'mustache')
        {
            $this->render_core = new \Nelliel\RenderCoreMustache($this->domain);
        }
        else if($core_id === 'DOM')
        {
            $this->render_core = new \Nelliel\RenderCoreDOM();
        }
        else
        {

        }
    }
}