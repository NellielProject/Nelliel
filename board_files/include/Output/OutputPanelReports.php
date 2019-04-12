<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelReports extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array(), bool $data_only = false)
    {
        $render_data = array();
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_reports_access'))
        {
            nel_derp(380, _gettext('You are not allowed to access the reports panel.'));
        }

        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $render_data['head'] = $output_head->render(['dotdot' => $dotdot]);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Reports')];
        $render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'extra_data' => $extra_data], true);

        if ($this->domain->id() !== '')
        {
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . REPORTS_TABLE . '" WHERE "board_id" = ? ORDER BY "report_id" DESC');
            $report_list = $this->database->executePreparedFetchAll($prepared, [$this->domain->id()],
                    PDO::FETCH_ASSOC);
        }
        else
        {
            $report_list = $this->database->executeFetchAll(
                    'SELECT * FROM "' . REPORTS_TABLE . '" ORDER BY "report_id" DESC', PDO::FETCH_ASSOC);
        }

        $bgclass = 'row1';
        $domains = array();

        foreach ($report_list as $report_info)
        {
            if (!isset($domains[$report_info['board_id']]))
            {
                $domains[$report_info['board_id']] = new \Nelliel\DomainBoard($report_info['board_id'], $this->database);
            }

            $report_data = array();
            $report_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $current_domain = $domains[$report_info['board_id']];
            $content_id = new \Nelliel\ContentID($report_info['content_id']);
            $base_domain = BASE_DOMAIN . pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);
            $board_web_path = '//' . $base_domain . '/' . rawurlencode($current_domain->reference('board_directory')) .
                    '/';
            $content_url = '';

            if ($content_id->isThread())
            {
                $content_url = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'render', 'action' => 'view-thread', 'thread' => $content_id->thread_id,
                            'content-id' => $content_id->getIDString(), 'board_id' => $report_info['board_id'],
                            'modmode' => 'true']);
                $report_data['is_content'] = false;
            }
            else if ($content_id->isPost())
            {
                $content_url = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'render', 'action' => 'view-thread', 'thread' => $content_id->thread_id,
                            'content-id' => $content_id->getIDString(), 'board_id' => $report_info['board_id'],
                            'modmode' => 'true']);
                $content_url .= '#t' . $content_id->thread_id . 'p' . $content_id->post_id;
                $report_data['is_content'] = false;
            }
            else if ($content_id->isContent())
            {
                $report_data['is_content'] = false;
                $prepared = $this->database->prepare(
                        'SELECT "filename" FROM "' . $current_domain->reference('content_table') .
                        '" WHERE "parent_thread" = ? AND post_ref = ? AND "content_order" = ?');
                $filename = $this->database->executePreparedFetch($prepared,
                        [$content_id->thread_id, $content_id->post_id, $content_id->order_id], PDO::FETCH_COLUMN);
                $src_web_path = $board_web_path . rawurlencode($current_domain->reference('src_dir')) . '/';
                $report_data['file_url'] = $src_web_path . $content_id->thread_id . '/' . $content_id->post_id . '/' .
                        rawurlencode($filename);

                $content_url = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'render', 'action' => 'view-thread', 'thread' => $content_id->thread_id,
                            'content-id' => $content_id->getIDString(), 'board_id' => $report_info['board_id'],
                            'modmode' => 'true']);
                $content_url .= '#t' . $content_id->thread_id . 'p' . $content_id->post_id;
            }

            $report_data['report_id'] = $report_info['report_id'];
            $report_data['board_id'] = $report_info['board_id'];
            $report_data['content_url'] = $content_url;
            $report_data['content_id'] = $report_info['content_id'];
            $report_data['reason'] = $report_info['reason'];
            $report_data['reporter_ip'] = $report_info['reporter_ip'];
            $report_data['dismiss_url'] = MAIN_SCRIPT . '?module=reports&board_id=' . $report_info['board_id'] .
                    '&action=dismiss&report_id=' . $report_info['report_id'];
            $render_data['reports_list'][] = $report_data;
        }

        $render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/reports_panel',
                $render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output($render_data, 'page', true);
        echo $output;
        return $output;
    }
}