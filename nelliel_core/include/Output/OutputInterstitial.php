<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

class OutputInterstitial extends OutputCore
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only, array $messages, array $links)
    {
        $this->renderSetup();
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);

        foreach($messages as $message)
        {
            $this->render_data['messages'][] = ['message' => $message];
        }

        foreach($links as $link)
        {
            $this->render_data['links'][] = $link;
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('basic_interstitial', $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        return $output;
    }
}