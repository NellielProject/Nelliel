<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelLogs extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/logs');
        $parameters['is_panel'] = true;
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $page = (int) $parameters['page'] ?? 1;
        $entries = $parameters['entries'] ?? 20;
        $row_offset = ($page > 1) ? ($page - 1) * $entries : 0;
        $log_set = $parameters['log_set'] ?? 'combined';
        $log_count = 0;
        $query = '';
        $panel = '';

        // TODO: Cache and possibly update this elsewhere instead of calling every time
        if ($log_set === 'public' || $log_set === 'combined') {
            $log_count += $this->database->executeFetch('SELECT COUNT(*) FROM "' . NEL_PUBLIC_LOGS_TABLE . '"',
                PDO::FETCH_COLUMN);
        }

        if ($log_set === 'system' || $log_set === 'combined') {
            $log_count += $this->database->executeFetch('SELECT COUNT(*) FROM "' . NEL_SYSTEM_LOGS_TABLE . '"',
                PDO::FETCH_COLUMN);
        }

        if ($log_set === 'system') {
            $panel = __('System Logs');
            $query = 'SELECT * FROM "' . NEL_SYSTEM_LOGS_TABLE . '" ORDER BY "time" DESC, "log_id" DESC LIMIT ? OFFSET ?';
        }

        if ($log_set === 'public') {
            $panel = __('Public Logs');
            $query = 'SELECT * FROM "' . NEL_PUBLIC_LOGS_TABLE . '" ORDER BY "time" DESC, "log_id" DESC LIMIT ? OFFSET ?';
        }

        if ($log_set === 'combined') {
            $panel = __('Combined Logs');
            $query = 'SELECT * FROM "' . NEL_SYSTEM_LOGS_TABLE . '" UNION ALL SELECT * FROM "' . NEL_PUBLIC_LOGS_TABLE .
                '"ORDER BY "time" DESC, "log_id" DESC LIMIT ? OFFSET ?';
        }

        $prepared = $this->database->prepare($query);
        $logs = $this->database->executePreparedFetchAll($prepared, [$entries, $row_offset], PDO::FETCH_ASSOC);
        $parameters['panel'] = $parameters['panel'] ?? $panel;
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $bgclass = 'row1';
        $this->render_data['log_entry_list'] = array();

        foreach ($logs as $log) {
            $log_data = array();
            $log_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $log_data['log_id'] = $log['log_id'];
            $log_data['event'] = $log['event'];
            $log_data['domain_id'] = $log['domain_id'];
            $log_data['user'] = $log['username'];
            $log_data['ip_address'] = nel_convert_ip_from_storage($log['ip_address']);
            $log_data['hashed_ip_address'] = $log['hashed_ip_address'];
            $log_data['time'] = $log['time'];
            $log_data['message'] = $log['message'];
            $this->render_data['log_entry_list'][] = $log_data;
        }

        $page_count = (int) ceil($log_count / $entries);
        $page_url = nel_build_router_url([$this->domain->id(), 'logs'], true) . '%d';
        $previous_url = ($page > 1) ? nel_build_router_url([$this->domain->id(), 'logs'], true) . '%d' : null;
        $next_url = nel_build_router_url([$this->domain->id(), 'logs'], true) . '%d';
        $pagination = new Pagination();
        $pagination->setPrevious(__('Previous'), $previous_url);
        $pagination->setNext(__('Next'), $next_url);
        $pagination->setPage('%d', $page_url);
        $pagination->setFirst('%d', $page_url);
        $pagination->setLast('%d', $page_url);
        $this->render_data['pagination'] = $pagination->generateNumerical(1, $page_count, $page);
        $this->render_data['system_logs_url'] = nel_build_router_url([$this->domain->id(), 'logs', 'system']);
        $this->render_data['public_logs_url'] = nel_build_router_url([$this->domain->id(), 'logs', 'public']);
        $this->render_data['combined_logs_url'] = nel_build_router_url([$this->domain->id(), 'logs', 'combined']);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}