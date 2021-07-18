<?php

declare(strict_types=1);

namespace Nelliel\Modules\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;

class OutputAboutNelliel extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('about_nelliel');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general($parameters, true);
        $this->render_data['nelliel_version'] = _gettext('Version: ') . NELLIEL_VERSION;
        $this->render_data['disclaimer_image_url'] = NEL_MEDIA_WEB_PATH . 'core/about/luna_canterlot_disclaimer.png';
        $this->render_data['disclaimer_alt_text'] = 'Luna Canterlot Voice';
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}