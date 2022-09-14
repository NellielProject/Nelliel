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
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Logs');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $page = $parameters['page'] ?? 1;
        $entries = $parameters['entries'] ?? 20;
        $row_offset = ($page > 1) ? $page * $entries : 0;
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $query = 'SELECT * FROM "' . NEL_LOGS_TABLE . '" ORDER BY "time" DESC, "log_id" DESC LIMIT ? OFFSET ?';
        $prepared = $this->database->prepare($query);
        $logs = $this->database->executePreparedFetchAll($prepared, [$entries, $row_offset], PDO::FETCH_ASSOC);
        $bgclass = 'row1';
        $this->render_data['log_entry_list'] = array();

        foreach ($logs as $log) {
            $log_data = array();
            $log_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $log_data['log_id'] = $log['log_id'];
            $log_data['level'] = intval($log['level']);
            $log_data['event'] = $log['event'];
            $log_data['username'] = $log['username'];
            $log_data['ip_address'] = nel_convert_ip_from_storage($log['ip_address']);
            $log_data['hashed_ip_address'] = $log['hashed_ip_address'];
            $log_data['time'] = $log['time'];
            $log_data['message'] = $log['message'];
            $this->render_data['log_entry_list'][] = $log_data;
        }

        $page_url = nel_build_router_url([$this->domain->id(), 'logs', $page]);
        $previous_url = ($page > 1) ? nel_build_router_url([$this->domain->id(), 'logs', $page]) : null;
        $next_url = nel_build_router_url([$this->domain->id(), 'logs', $page + 1]);
        $page_count = $parameters['page_count'] ?? 1;
        $pagination_object = new Pagination();
        $pagination_object->setPrevious(_gettext('<<'), $previous_url);
        $pagination_object->setNext(_gettext('>>'), $next_url);
        $pagination_object->setPage((string) $page, $page_url);
        $this->render_data['pagination'] = $pagination_object->generateNumerical(1, $page_count, $page);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}