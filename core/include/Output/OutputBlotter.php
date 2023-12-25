<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputBlotter extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('blotter');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $this->render_data['blotter_entries'] = $this->blotterList();
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->general([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        $this->file_handler->writeFile(NEL_PUBLIC_PATH . 'blotter.html', $output);
    }

    private function blotterList(int $limit = 0)
    {
        $database = $this->domain->database();
        $blotter_entries = $database->executeFetchAll('SELECT * FROM "' . NEL_BLOTTER_TABLE . '" ORDER BY "time" DESC',
            PDO::FETCH_ASSOC);
        $limit_counter = 0;
        $entry_list = array();

        foreach ($blotter_entries as $entry) {
            if ($limit !== 0 && $limit_counter >= $limit) {
                break;
            }

            $info = array();
            $info['text'] = $entry['text'];
            $info['time'] = $this->domain->domainDateTime(intval($entry['time']))->format(
                $this->site_domain->setting('blotter_time_format'));
            $entry_list[] = $info;
            $limit_counter ++;
        }

        return $entry_list;
    }
}