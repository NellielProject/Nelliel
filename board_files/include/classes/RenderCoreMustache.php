<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class RenderCoreMustache extends RenderCore
{
    private $domain;
    private $mustache_engine;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->output_filter = new OutputFilter();
        $this->file_handler = new FileHandler();
        $this->template_loaders['file'] = new \Mustache_Loader_FilesystemLoader($this->domain->templatePath(), ['extension' => '.html']);
        $this->mustache_engine = new \Mustache_Engine(['loader' => $this->template_loaders['file'], 'partials_loader' => $this->template_loaders['file']]);
    }

    public function renderEngine()
    {
        return $this->mustache_engine;
    }

    public function loadTemplateFromFile(string $file)
    {
        $template = $this->template_loaders['file']->load($file);
        return $template;
    }

    public function renderFromTemplateFile(string $file, array $render_data)
    {
        return $this->mustache_engine->render($file, $render_data);
    }
}
