<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

class OutputAboutPage extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $this->render_core->startTimer();
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $this->render_core->appendToOutput($output_header->render(['header_type' => 'general', 'dotdot' => '']));
        $render_input['nelliel_version'] = _gettext('Version: ') . NELLIEL_VERSION;
        $render_input['disclaimer_image_url'] = IMAGES_WEB_PATH . 'wat/luna_canterlot_disclaimer.png';
        $this->render_core->appendToOutput($this->render_core->renderFromTemplateFile('about_nelliel', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        echo $this->render_core->getOutput();
        nel_clean_exit();
    }
}