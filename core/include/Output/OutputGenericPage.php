<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;

class OutputGenericPage extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(string $title, string $body_text, string $markup_type, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('generic_body');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render(['page_title' => $title], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general(array(), true);

        if($markup_type === 'none') {
            $this->render_data['body_output'] = htmlspecialchars($body_text, ENT_NOQUOTES, 'UTF-8');
            $this->render_data['preformatted'] = true;
        }

        if($markup_type === 'html') {
            $this->render_data['body_output'] = $body_text;
        }

        if($markup_type === 'imageboard') {
            $markup = new Markup($this->database);
            $this->render_data['body_output'] = $markup->parseText($body_text, $this->domain);
            $this->render_data['preformatted'] = true;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->general([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);

        if (!$this->writeMode()) {
            echo $output;
        }

        return $output;
    }
}