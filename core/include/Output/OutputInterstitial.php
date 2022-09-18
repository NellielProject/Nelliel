<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;

class OutputInterstitial extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function basic(array $parameters, bool $data_only, array $messages, array $links)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('interstitials/basic');
        $is_manage = $parameters['is_manage'] ?? false;
        $page_title = $parameters['page_title'] ?? $this->domain->reference('title');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render(['page_title' => $page_title], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);

        if ($is_manage) {
            $this->render_data['header'] = $output_header->manage($parameters, true);
        } else {
            $this->render_data['header'] = $output_header->general($parameters, true);
        }

        foreach ($messages as $message) {
            $this->render_data['messages'][] = ['message' => $message];
        }

        foreach ($links as $link) {
            $this->render_data['links'][] = $link;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        return $output;
    }

    public function confirm(array $parameters, bool $data_only, array $messages, array $yes_info, array $no_info)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('interstitials/confirm');
        $is_manage = $parameters['is_manage'] ?? false;
        $page_title = $parameters['page_title'] ?? $this->domain->reference('title');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render(['page_title' => $page_title], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);

        if ($is_manage) {
            $this->render_data['header'] = $output_header->manage($parameters, true);
        } else {
            $this->render_data['header'] = $output_header->general($parameters, true);
        }

        foreach ($messages as $message) {
            $this->render_data['messages'][] = ['message' => $message];
        }

        $this->render_data['yes_url'] = $yes_info['url'];
        $this->render_data['yes_text'] = $yes_info['text'];
        $this->render_data['no_url'] = $no_info['url'];
        $this->render_data['no_text'] = $no_info['text'];
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        return $output;
    }
}