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
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->render_data['show_styles'] = ($parameters['show_styles']) ?? true;

        if ($this->render_data['show_styles'])
        {
            $output_menu = new OutputMenu($this->domain, $this->write_mode);
            $this->render_data['styles'] = $output_menu->styles([], true);
        }

        $this->render_data['nelliel_version'] = NELLIEL_VERSION;
        $output = $this->output('footer', $data_only, true);
        return $output;
    }
}