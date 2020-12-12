<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputMenu extends OutputCore
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
        $output = array();

        if (!isset($parameters['menu']))
        {
            return;
        }

        switch ($parameters['menu'])
        {
            case 'styles':
                $output = $this->styles($parameters, $data_only);
                break;
        }

        return $output;
    }

    private function styles(array $parameters, bool $data_only)
    {
        $render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $dotdot = $parameters['dotdot'] ?? '';
        $styles = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_ASSETS_TABLE . '" WHERE "type" = \'style\' ORDER BY "entry", "is_default" DESC',
                PDO::FETCH_ASSOC);
        $front_end_data = new \Nelliel\FrontEndData($this->database);

        foreach ($styles as $style)
        {
            $style_data = array();
            $info = json_decode($style['info'], true);
            $style_data['stylesheet'] = ($style['is_default']) ? 'stylesheet' : 'alternate stylesheet';
            $style_data['style_id'] = $style['asset_id'];

            if ($front_end_data->styleIsCore($style['asset_id']))
            {
                $style_data['stylesheet_url'] = NEL_CORE_STYLES_WEB_PATH . $info['directory'] . '/' . $info['main_file'];
            }
            else
            {
                $style_data['stylesheet_url'] = NEL_CUSTOM_STYLES_WEB_PATH . $info['directory'] . '/' .
                        $info['main_file'];
            }

            $style_data['style_name'] = $info['name'];
            $render_data[] = $style_data;
        }

        return $render_data;
    }
}