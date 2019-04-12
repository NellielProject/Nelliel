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

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array(), bool $data_only = false)
    {
        $render_data = array();
        $dotdot = ($parameters['dotdot']) ?? array();
        $show_timer = ($parameters['show_timer']) ?? true;
        $render_data['show_styles'] = ($parameters['show_styles']) ?? true;
        $output_menu = new OutputMenu($this->domain);

        if ($render_data['show_styles'])
        {
            $render_data['styles'] = $output_menu->render(['menu' => 'styles', 'dotdot' => $dotdot]);
        }

        $render_data['nelliel_version'] = NELLIEL_VERSION;
        $render_data['js_ui_url'] = $dotdot . SCRIPTS_WEB_PATH . 'ui.js';
        $output = $this->output($render_data, 'footer', false, $data_only);
        return $output;
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