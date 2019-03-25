<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputFooter extends OutputCore
{
    private $database;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        // Temp
        $this->render_instance = $this->domain->renderInstance();

        $template_loader = new \Mustache_Loader_FilesystemLoader($this->domain->templatePath(), ['extension' => '.html']);
        $render_instance = new \Mustache_Engine(['loader' => $template_loader]);
        $template_loader->load('footer');
        $dotdot = ($parameters['dotdot']) ?? array();
        $styles = ($parameters['generate_styles']) ?? false;

        if ($styles)
        {
            $render_input['styles'] = $this->buildStyles($dotdot);
            $render_input['show_styles'] = true;
        }

        $render_input['nelliel_vertsion'] = NELLIEL_VERSION;
        $render_input['js_ui_url'] = $dotdot . SCRIPTS_WEB_PATH . 'ui.js';

        if($this->domain->setting('display_render_timer'))
        {
            $time = round($this->domain->renderInstance()->endRenderTimer(), 4);
            $render_input['render_timer'] = sprintf(_gettext('This page was created in %s seconds.'), $time);
        }

        $this->domain->renderInstance()->appendHTML($render_instance->render('footer', $render_input));
    }

    public function buildStyles(string $dotdot)
    {
        $styles = $this->database->executeFetchAll(
                'SELECT * FROM "' . ASSETS_TABLE . '" WHERE "type" = \'style\' ORDER BY "entry", "is_default" DESC',
                PDO::FETCH_ASSOC);
        $style_set = array();

        foreach ($styles as $style)
        {
            $style_data = array();
            $info = json_decode($style['info'], true);
            $style_data['stylesheet'] = ($style['is_default']) ? 'stylesheet' : 'alternate stylesheet';
            $style_data['style_id'] = $style['id'];
            $style_data['stylesheet_url'] = $dotdot . STYLES_WEB_PATH . $info['directory'] . '/' . $info['main_file'];
            $style_data['style_name'] = $info['name'];
            $style_set[] = $style_data;
        }

        return $style_set;
    }
}