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
    private $escaper;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->output_filter = new OutputFilter();
        $this->file_handler = new FileHandler();
        $this->escaper = new \phpDOMExtend\DOMEscaper();
        $this->template_loaders['file'] = new \Mustache_Loader_FilesystemLoader($this->domain->templatePath(),
                ['extension' => '.html']);

        if (USE_MUSTACHE_CACHE)
        {
            $this->mustache_engine = new \Mustache_Engine(
                    ['loader' => $this->template_loaders['file'],
                        'partials_loader' => $this->template_loaders['file'], 'cache' => CACHE_FILE_PATH . 'mustache']);
        }
        else
        {
            $this->mustache_engine = new \Mustache_Engine(
                    ['loader' => $this->template_loaders['file'],
                        'partials_loader' => $this->template_loaders['file']]);
        }

        $this->mustache_engine->addHelper('esc',
                [
                    'html' => function ($value)
                    {
                        return $this->escapeString($value, 'html');
                    },
                    'attr' => function ($value)
                    {
                        return $this->escapeString($value, 'attr');
                    },
                    'url' => function ($value)
                    {
                        return $this->escapeString($value, 'url');
                    },
                    'js' => function ($value)
                    {
                        return $this->escapeString($value, 'js');
                    },
                    'css' => function ($value)
                    {
                        return $this->escapeString($value, 'css');
                    }]);
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

    public function escapeString(string $string = null, string $type)
    {
        $this->escaper->doEscaping($string, $type);
        return $string;
    }
}
