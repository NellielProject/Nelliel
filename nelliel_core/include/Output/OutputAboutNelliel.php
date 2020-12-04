<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

class OutputAboutNelliel extends OutputCore
{

    function __construct(Domain $domain, bool $write_mode)
    {
        $this->domain = $domain;
        $this->write_mode = $write_mode;
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->render(['header_type' => 'general', 'dotdot' => $dotdot], true);
        $this->render_data['nelliel_version'] = _gettext('Version: ') . NELLIEL_VERSION;
        $this->render_data['disclaimer_image_url'] = NEL_CORE_IMAGES_WEB_PATH . 'about/luna_canterlot_disclaimer.png';
        $this->render_data['disclaimer_alt_text'] = 'Luna Canterlot Voice';
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('about_nelliel', $data_only, true);
        echo $output;
        return $output;
    }
}