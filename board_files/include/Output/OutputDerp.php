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

    public function render(array $parameters = array())
    {
        $this->startTimer();
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $this->render_core->appendToOutput($output_header->render(['header_type' => 'general', 'dotdot' => '']));
        $diagnostic = $parameters['diagnostic'];
        $render_input = array();
        $render_input['error_id'] = $diagnostic['error_id'];
        $render_input['error_message'] = $diagnostic['error_message'];
        $render_input['error_data'] = '';
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

        $render_input['return_link'] = $return_link;

        $this->render_core->appendToOutput($this->render_core->renderFromTemplateFile('derp', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        echo $this->render_core->getOutput();
        nel_clean_exit();
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