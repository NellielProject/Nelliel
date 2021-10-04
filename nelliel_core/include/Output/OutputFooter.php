<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;

class OutputFooter extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->render_data['nelliel_version'] = NELLIEL_VERSION;

        if (!nel_true_empty($this->domain->setting('board_footer_text')))
        {
            foreach ($this->output_filter->newlinesToArray($this->domain->setting('board_footer_text')) as $line)
            {
                $this->render_data['board_footer_lines'][]['text'] = htmlspecialchars($line);
            }
        }

        if (!nel_true_empty($this->site_domain->setting('site_footer_text')))
        {
            foreach ($this->output_filter->newlinesToArray($this->site_domain->setting('site_footer_text')) as $line)
            {
                $this->render_data['site_footer_lines'][]['text'] = htmlspecialchars($line);
            }
        }

        $output = $this->output('footer', $data_only, true, $this->render_data);
        return $output;
    }
}