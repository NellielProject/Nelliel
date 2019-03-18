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
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $final_output = '';

        // Temp
        $this->render_instance = $this->domain->renderInstance();
        $this->render_instance->startRenderTimer();

        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $final_output .= $output_header->render(['header_type' => 'general', 'dotdot' => '']);
        $template_loader = new \Mustache_Loader_FilesystemLoader($this->domain->templatePath(), ['extension' => '.html']);
        $render_instance = new \Mustache_Engine(['loader' => $template_loader]);
        $template_loader->load('derp2');
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

        // Temp
        $this->render_instance->appendHTML($render_instance->render('derp2', $render_input));

        //$final_output .= $render_instance->render('derp2', $render_input);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();

        //echo $final_output;


        /*$this->prepare('derp2.html');
        $diagnostic = $parameters['diagnostic'];
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $output_header->render(['header_type' => 'general', 'dotdot' => '']);
        $this->dom->getElementById('error-id')->setContent($diagnostic['error_id']);
        $this->dom->getElementById('error-message')->setContent($diagnostic['error_message']);
        $this->dom->getElementById('error-data')->setContent(''); // TODO: This actually have something
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
                $return_link = $this->dom->getElementById('return-link')->extSetAttribute('href',
                        BASE_WEB_PATH . $this->domain->setting('home_page'));
            }
            else
            {
                $return_link = $this->dom->getElementById('return-link')->extSetAttribute('href',
                        BASE_WEB_PATH . '/' . $this->domain->reference('board_directory') . '/');
            }
        }

        $this->domain->translator()->translateDom($this->dom, $this->domain->setting('language'));
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();*/
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