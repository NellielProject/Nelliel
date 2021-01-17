<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;

class OutputDerp extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('derp');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $diagnostic = $parameters['diagnostic'];
        $this->render_data['error_id'] = $diagnostic['error_id'];
        $this->render_data['error_message'] = $diagnostic['error_message'];
        $this->render_data['error_data'] = '';

        if ($this->domain->id() === Domain::SITE)
        {
            $return_url = NEL_BASE_WEB_PATH . $this->domain->setting('home_page');
        }
        else
        {
            $return_url = NEL_BASE_WEB_PATH . $this->domain->reference('board_directory');
        }

        if ($this->session->inModmode($this->domain))
        {
            if ($this->domain->id() === Domain::SITE)
            {
                ; // TODO: Figure out this one
            }
            else
            {
                $return_url = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                        http_build_query(
                                ['module' => 'render', 'actions' => 'view-index', 'index' => '0',
                                    'board-id' => $this->domain->id(), 'modmode' => 'true']);
            }
        }

        $this->render_data['return_url'] = $return_url;
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        return $output;
    }
}