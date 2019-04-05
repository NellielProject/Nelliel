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
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $dotdot = ($parameters['dotdot']) ?? array();
        $generate_styles = ($parameters['generate_styles']) ?? false;

        if ($generate_styles)
        {
            $render_input['styles'] = $this->buildStyles($dotdot);
            $render_input['show_styles'] = true;
        }

        $render_input['nelliel_vertsion'] = NELLIEL_VERSION;
        $render_input['js_ui_url'] = $dotdot . SCRIPTS_WEB_PATH . 'ui.js';

        if($this->domain->setting('display_render_timer'))
        {
            $time = round($this->render_core->endTimer(), 4);
            $render_input['render_timer'] = sprintf(_gettext('This page was created in %s seconds.'), $time);
        }

        $this->render_core->appendToOutput($this->render_core->renderFromTemplateFile('footer', $render_input));
        return $this->render_core->getOutput();
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