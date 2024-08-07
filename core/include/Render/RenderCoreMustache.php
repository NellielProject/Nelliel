<?php
declare(strict_types = 1);

namespace Nelliel\Render;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Output\Filter;
use Mustache_Engine;
use phpDOMExtend\DOMEscaper;

class RenderCoreMustache extends RenderCore
{
    private Domain $domain;
    private Mustache_Engine $mustache_engine;
    private DOMEscaper $escaper;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->output_filter = new Filter();
        $this->file_handler = nel_utilities()->fileHandler();
        $this->escaper = new DOMEscaper();
        $this->template_loaders['file'] = new FileSystemLoader($this->domain->templatePath(), ['extension' => '.html']);
        $this->newMustache();
    }

    private function newMustache()
    {
        $options = array();
        $options['pragmas'] = [Mustache_Engine::PRAGMA_FILTERS];

        if (NEL_USE_MUSTACHE_CACHE) {
            $options['cache'] = NEL_CACHE_FILES_PATH . 'mustache';
        }

        $this->mustache_engine = new Mustache_Engine($options);
        $this->mustache_engine->setLoader($this->template_loaders['file']);
        $this->mustache_engine->setPartialsLoader($this->template_loaders['file']);
        $this->mustache_engine->addHelper('esc',
            ['html' => function ($value) {
                return $this->escapeString($value, 'html');
            }, 'attr' => function ($value) {
                return $this->escapeString($value, 'attr');
            }, 'url' => function ($value) {
                return $this->escapeString($value, 'url');
            }, 'js' => function ($value) {
                return $this->escapeString($value, 'js');
            }, 'css' => function ($value) {
                return $this->escapeString($value, 'css');
            }]);

        $this->mustache_engine->addHelper('gettext', function ($msgid) {
            return _gettext($msgid);
        });
    }

    public function renderEngine(): Mustache_Engine
    {
        return $this->mustache_engine;
    }

    public function loadTemplateFromFile(string $file): string
    {
        $template = $this->template_loaders['file']->load($file);
        return $template;
    }

    public function renderFromTemplateFile(string $file, array $render_data): string
    {
        return $this->mustache_engine->render($file, $render_data);
    }

    public function escapeString(string $string = null, string $type): string
    {
        $this->escaper->doEscaping($string, $type);
        return $string;
    }
}
