<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

class OutputDerp extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot], true);
        $diagnostic = $parameters['diagnostic'];
        $this->render_data['error_id'] = $diagnostic['error_id'];
        $this->render_data['error_message'] = $diagnostic['error_message'];
        $this->render_data['error_data'] = '';
        $session = new \Nelliel\Session();

        if ($session->inModmode($this->domain))
        {
            if ($this->domain->id() === '')
            {
                ; // TODO: Figure out this one
            }
            else
            {
                $return_link = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'render', 'action' => 'view-index', 'index' => '0',
                        'board_id' => $this->domain->id(), 'modmode' => 'true']);
            }
        }
        else
        {
            if ($this->domain->id() === '')
            {
                $return_link = BASE_WEB_PATH . $this->domain->setting('home_page');
            }
            else
            {
                $return_link = BASE_WEB_PATH . $this->domain->reference('board_directory');
            }
        }

        $this->render_data['return_link'] = $return_link;
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('derp',
                $this->render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('page', $data_only, true);
        return $output;
    }

    public function renderSimple(array $diagnostic)
    {
        echo _gettext('oh god how did this get in here');
        echo '<br>';
        echo _gettext('Error ID: ') . $diagnostic['error_id'];
        echo '<br>';
        echo _gettext('Error Message: ') . $diagnostic['error_message'];
        die();
    }
}