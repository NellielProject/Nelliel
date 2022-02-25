<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputNotices extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('notices');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $this->render_data['notices'] = $this->noticeList();
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        return $output;
    }

    private function noticeList(int $limit = 0)
    {
        $database = $this->domain->database();
        $blotter_entries = $database->executeFetchAll(
            'SELECT * FROM "' . NEL_NOTICEBOARD_TABLE . '" ORDER BY "time" DESC', PDO::FETCH_ASSOC);
        $limit_counter = 0;
        $entry_list = array();

        foreach ($blotter_entries as $entry) {
            if ($limit !== 0 && $limit_counter >= $limit) {
                break;
            }

            $info = array();
            $info['notice_id'] = $entry['notice_id'];
            $info['user'] = $entry['username'];
            $info['subject'] = $entry['subject'];
            $info['time'] = date('Y/m/d', intval($entry['time']));
            $info['message'] = $entry['message'];
            $info['url'] = nel_build_router_url([Domain::SITE, 'account', 'noticeboard']) . '#' . $entry['notice_id'];
            $entry_list[] = $info;
            $limit_counter ++;
        }

        return $entry_list;
    }
}