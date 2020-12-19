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
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $this->render_data['nelliel_version'] = _gettext('Version: ') . NELLIEL_VERSION;
        $this->render_data['disclaimer_image_url'] = NEL_IMAGES_WEB_PATH . 'about/luna_canterlot_disclaimer.png';
        $this->render_data['disclaimer_alt_text'] = 'Luna Canterlot Voice';
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('about_nelliel', $data_only, true);
        echo $output;
        return $output;
    }
}