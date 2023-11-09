<?php
declare(strict_types = 1);

namespace Nelliel\Render;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class RenderCoreSimple extends RenderCore
{
    private $template_file_loader;
    private $mustache_engine;

    function __construct(string $base_directory)
    {
        $options = array();
        $options['pragmas'] = [\Mustache_Engine::PRAGMA_FILTERS];
        $this->mustache_engine = new \Mustache_Engine($options);
        $this->template_file_loader = new \Mustache_Loader_FilesystemLoader($base_directory, ['extension' => '.html']);
        $this->mustache_engine->setLoader($this->template_file_loader);
        $this->mustache_engine->setPartialsLoader($this->template_file_loader);
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
    }

    public function loadTemplateFromFile(string $file): string
    {
        return $this->template_file_loader->load($file);
    }

    public function renderFromTemplateFile(string $file, array $render_data = array()): string
    {
        return $this->mustache_engine->render($file, $render_data);
    }
}
