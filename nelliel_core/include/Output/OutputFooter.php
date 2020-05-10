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

    function __construct(Domain $domain, bool $write_mode)
    {
        $this->domain = $domain;
        $this->write_mode = $write_mode;
        $this->database = $domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $dotdot = ($parameters['dotdot']) ?? array();
        $show_timer = ($parameters['show_timer']) ?? true;
        $this->render_data['show_styles'] = ($parameters['show_styles']) ?? true;
        $output_menu = new OutputMenu($this->domain, $this->write_mode);

        if ($this->render_data['show_styles'])
        {
            $this->render_data['styles'] = $output_menu->render(['menu' => 'styles', 'dotdot' => $dotdot], true);
        }

        $this->render_data['nelliel_version'] = NELLIEL_VERSION;
        $output = $this->output('footer', $data_only, true);
        return $output;
    }

    public function buildStyles(string $dotdot)
    {
        $styles = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_ASSETS_TABLE . '" WHERE "type" = \'style\' ORDER BY "entry", "is_default" DESC',
                PDO::FETCH_ASSOC);
        $style_set = array();

        foreach ($styles as $style)
        {
            $style_data = array();
            $info = json_decode($style['info'], true);
            $style_data['stylesheet'] = ($style['is_default']) ? 'stylesheet' : 'alternate stylesheet';
            $style_data['style_id'] = $style['id'];
            $style_data['stylesheet_url'] = $dotdot . NEL_CUSTOM_STYLES_WEB_PATH . $info['directory'] . '/' . $info['main_file'];
            $style_data['style_name'] = $info['name'];
            $style_set[] = $style_data;
        }

        return $style_set;
    }
}