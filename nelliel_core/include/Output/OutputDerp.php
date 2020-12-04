<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

class OutputDerp extends OutputCore
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
        $diagnostic = $parameters['diagnostic'];
        $this->render_data['error_id'] = $diagnostic['error_id'];
        $this->render_data['error_message'] = $diagnostic['error_message'];
        $this->render_data['error_data'] = '';
        $session = new \Nelliel\Account\Session();

        if ($this->domain->id() === '_site_')
        {
            $return_url = NEL_BASE_WEB_PATH . $this->domain->setting('home_page');
        }
        else
        {
            $return_url = NEL_BASE_WEB_PATH . $this->domain->reference('board_directory');
        }

        if ($session->inModmode($this->domain))
        {
            if ($this->domain->id() === '_site_')
            {
                ; // TODO: Figure out this one
            }
            else
            {
                $return_url = NEL_MAIN_SCRIPT_QUERY .
                        http_build_query(
                                ['module' => 'render', 'actions' => 'view-index', 'index' => '0',
                                    'board_id' => $this->domain->id(), 'modmode' => 'true']);
            }
        }

        $this->render_data['return_url'] = $return_url;
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('derp', $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        return $output;
    }
}