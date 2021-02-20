<?php

declare(strict_types=1);

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;
use PDO;

class OutputPanelReports extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/reports');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Reports');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);

        if ($this->domain->id() !== Domain::SITE)
        {
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . NEL_REPORTS_TABLE . '" WHERE "board_id" = ? ORDER BY "report_id" DESC');
            $report_list = $this->database->executePreparedFetchAll($prepared, [$this->domain->id()], PDO::FETCH_ASSOC);
        }
        else
        {
            $report_list = $this->database->executeFetchAll(
                    'SELECT * FROM "' . NEL_REPORTS_TABLE . '" ORDER BY "report_id" DESC', PDO::FETCH_ASSOC);
        }

        $bgclass = 'row1';
        $domains = array();

        foreach ($report_list as $report_info)
        {
            if (!isset($domains[$report_info['board_id']]))
            {
                $domains[$report_info['board_id']] = new \Nelliel\Domains\DomainBoard($report_info['board_id'],
                        $this->database);
            }

            $report_data = array();
            $report_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $current_domain = $domains[$report_info['board_id']];
            $content_id = new ContentID($report_info['content_id']);
            $content_url = '';

            if ($content_id->isThread())
            {
                $content_url = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                        http_build_query(
                                ['module' => 'render', 'actions' => 'view-thread', 'thread' => $content_id->threadID(),
                                    'content-id' => $content_id->getIDString(), 'board-id' => $report_info['board_id'],
                                    'modmode' => 'true']);
                $report_data['is_content'] = false;
            }
            else if ($content_id->isPost())
            {
                $content_url = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                        http_build_query(
                                ['module' => 'render', 'actions' => 'view-thread', 'thread' => $content_id->threadID(),
                                    'content-id' => $content_id->getIDString(), 'board-id' => $report_info['board_id'],
                                    'modmode' => 'true']);
                $content_url .= '#t' . $content_id->threadID() . 'p' . $content_id->postID();
                $report_data['is_content'] = false;
            }
            else if ($content_id->isContent())
            {
                $report_data['is_content'] = false;
                $prepared = $this->database->prepare(
                        'SELECT "filename" FROM "' . $current_domain->reference('content_table') .
                        '" WHERE "parent_thread" = ? AND post_ref = ? AND "content_order" = ?');
                $filename = $this->database->executePreparedFetch($prepared,
                        [$content_id->threadID(), $content_id->postID(), $content_id->orderID()], PDO::FETCH_COLUMN);
                $report_data['file_url'] = $current_domain->reference('src_web_path') . $content_id->threadID() . '/' .
                        $content_id->postID() . '/' . $filename;

                $content_url = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                        http_build_query(
                                ['module' => 'render', 'actions' => 'view-thread', 'thread' => $content_id->threadID(),
                                    'content-id' => $content_id->getIDString(), 'board-id' => $report_info['board_id'],
                                    'modmode' => 'true']);
                $content_url .= '#t' . $content_id->threadID() . 'p' . $content_id->postID();
            }

            $report_data['report_id'] = $report_info['report_id'];
            $report_data['board_id'] = $report_info['board_id'];
            $report_data['content_url'] = $content_url;
            $report_data['content_id'] = $report_info['content_id'];
            $report_data['reason'] = $report_info['reason'];
            $report_data['reporter_ip'] = @inet_pton($report_info['reporter_ip']);
            $report_data['dismiss_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=reports&board-id=' .
                    $report_info['board_id'] . '&actions=remove&report-id=' . $report_info['report_id'];
            $this->render_data['reports_list'][] = $report_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}