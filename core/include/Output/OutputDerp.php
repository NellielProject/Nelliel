<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\ReturnLink;
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
        $diagnostic = $parameters['diagnostic'] ?? array();
        $context = $parameters['context'] ?? array();
        $return_link = $context['return_link'] ?? new ReturnLink();
        $this->render_data['error_id'] = $diagnostic['error_id'];
        $this->render_data['error_message'] = $diagnostic['error_message'];
        $this->render_data['error_data'] = '';

        if ($return_link->ready()) {
            $this->render_data['return_link_url'] = $return_link->URL();
            $this->render_data['return_link_text'] = $return_link->text();
            $this->render_data['show_return_link'] = true;
        } else {
            // TODO: Update other areas and eliminate this
            if ($this->domain->id() === Domain::SITE) {
                $this->render_data['return_link_url'] = $this->domain->reference('home_page');
            } else {
                if ($this->session->inModmode($this->domain)) {
                    $this->render_data['return_link_url'] = nel_build_router_url([$this->domain->id()], true, 'modmode');
                } else {
                    $this->render_data['return_link_url'] = NEL_BASE_WEB_PATH .
                        $this->domain->reference('board_directory');
                }
            }

            $this->render_data['return_link_text'] = __('Return');
            $this->render_data['show_return_link'] = true;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->general([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        return $output;
    }
}