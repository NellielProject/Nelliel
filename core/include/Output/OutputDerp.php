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
        $this->setBodyTemplate('derp');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render(
            ['title' => $this->site_domain->setting('error_message_header')], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $diagnostic = $parameters['diagnostic'] ?? array();
        $context = $parameters['context'] ?? array();
        $return_link = $context['return_link'] ?? new ReturnLink();
        $this->render_data['error_header'] = $this->site_domain->setting('error_message_header');
        $this->render_data['error_id'] = $diagnostic['error_id'] ?? 0;
        $this->render_data['error_message'] = $diagnostic['error_message'] ?? '';
        $this->render_data['error_data'] = '';

        if (isset($context['plugin_name'])) {
            $this->render_data['plugin_error'] = true;
            $this->render_data['plugin_name'] = $context['plugin_name'];
        }

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
                    $this->render_data['return_link_url'] = nel_build_router_url([$this->domain->uri()], true,
                        'modmode');
                } else {
                    $this->render_data['return_link_url'] = NEL_BASE_WEB_PATH .
                        $this->domain->reference('board_directory');
                }
            }

            $this->render_data['return_link_text'] = __('Return');
            $this->render_data['show_return_link'] = true;
        }

        $image_set = $this->domain->frontEndData()->getImageSet($this->site_domain->setting('error_image_set'));
        $web_path = $image_set->getWebPath('error', strval($diagnostic['error_id']), true);

        if ($web_path === '') {
            $web_path = $image_set->getWebPath('error', 'default', true);
        }

        $this->render_data['show_image'] = $this->site_domain->setting('show_error_images');
        $this->render_data['image_url'] = $web_path;
        $this->render_data['image_alt'] = sprintf(__('Error %s image'), $diagnostic['error_id']);
        $this->render_data['image_max_size'] = $this->site_domain->setting('error_image_max_size');
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->general([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        return $output;
    }
}