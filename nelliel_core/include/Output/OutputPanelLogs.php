<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelLogs extends OutputCore
{

    function __construct(Domain $domain, bool $write_mode)
    {
        $this->domain = $domain;
        $this->write_mode = $write_mode;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $user = $parameters['user'];
        $log_type = $parameters['log_type'] ?? '';
        $page = $parameters['page'] ?? 0;
        $entries = $parameters['entries'] ?? 20;
        $row_offset = $page * $entries;

        if (!$user->checkPermission($this->domain, 'perm_manage_file_filters'))
        {
            nel_derp(341, _gettext('You are not allowed to access the File Filters panel.'));
        }

        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Logs')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'manage_headers' => $manage_headers], true);

        switch ($log_type)
        {
            case 'staff':
                $query = '(SELECT * FROM "' . NEL_LOGS_TABLE .
                        '") ORDER BY "time" DESC, "entry" DESC LIMIT ? OFFSET ?';
                break;

            case 'system':
                $query = '(SELECT * FROM "' . NEL_LOGS_TABLE .
                        '") ORDER BY "time" DESC, "entry" DESC LIMIT ? OFFSET ?';
                break;

            default:
                $query = '(SELECT * FROM "' . NEL_LOGS_TABLE . '")
                   UNION (SELECT * FROM "' . NEL_LOGS_TABLE .
                        '") ORDER BY "time" DESC, "entry" DESC LIMIT ? OFFSET ?';
                break;
        }

        $prepared = $this->database->prepare($query);
        $logs = $this->database->executePreparedFetchAll($prepared, [$entries, $row_offset], PDO::FETCH_ASSOC);
        $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(['module' => 'admin', 'section' => 'file-filters', 'actions' => 'add']);
        $bgclass = 'row1';
        $this->render_data['log_entry_list'] = array();

        foreach ($logs as $log)
        {
            $log_data = array();
            $log_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $log_data['entry'] = $log['entry'];
            $log_data['level'] = intval($log['level']);
            $log_data['event_id'] = $log['event_id'];
            $log_data['originator'] = $log['originator'];
            $log_data['ip_address'] = @inet_ntop($log['ip_address']);
            $log_data['hashed_ip_address'] = bin2hex($log['hashed_ip_address']);
            $log_data['time'] = $log['time'];
            $log_data['message'] = $log['message'];
            $this->render_data['log_entry_list'][] = $log_data;
        }

        $page_format = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=logs&page=%d';
        $page_count = $parameters['page_count'] ?? 1;
        $page = $parameters['page'] ?? 1;
        $pagination_object = new \Nelliel\Pagination();
        $pagination_object->setPrevious(_gettext('<<'));
        $pagination_object->setNext(_gettext('>>'));
        $pagination_object->setPage('%d', $page_format);
        $this->render_data['pagination'] = $pagination_object->generateNumerical(1, $page_count, $page);
        $this->render_data['staff_logs_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=logs&log-type=staff';
        $this->render_data['system_logs_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=logs&log-type=system';
        $this->render_data['all_logs_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=logs&log-type=all';
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/logs_panel', $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}