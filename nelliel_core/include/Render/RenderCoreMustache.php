<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

class RenderCoreMustache extends RenderCore
{
    private $domain;
    private $mustache_engine;
    private $escaper;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->output_filter = new Filter();
        $this->file_handler = nel_utilities()->fileHandler();
        $this->escaper = new \phpDOMExtend\DOMEscaper();
        $this->template_loaders['file'] = new \Mustache_Loader_FilesystemLoader($this->domain->templatePath(),
                ['extension' => '.html']);

        if (NEL_USE_MUSTACHE_CACHE)
        {
            $this->mustache_engine = new \Mustache_Engine(
                    ['loader' => $this->template_loaders['file'],
                        'partials_loader' => $this->template_loaders['file'], 'cache' => NEL_CACHE_FILES_PATH . 'mustache']);
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
