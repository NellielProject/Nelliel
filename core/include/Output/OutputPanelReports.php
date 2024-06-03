<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

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
        $this->setBodyTemplate('panels/reports');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Reports');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);

        if ($this->domain->id() === Domain::SITE) {
            $report_list = array();
        } else if ($this->domain->id() === Domain::GLOBAL) {
            $report_list = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_REPORTS_TABLE . '" ORDER BY "report_id" DESC', PDO::FETCH_ASSOC);
        } else {
            $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_REPORTS_TABLE . '" WHERE "board_id" = ? ORDER BY "report_id" DESC');
            $report_list = $this->database->executePreparedFetchAll($prepared, [$this->domain->id()], PDO::FETCH_ASSOC);
        }

        $bgclass = 'row1';
        $domains = array();

        foreach ($report_list as $report_info) {
            if (!isset($domains[$report_info['board_id']])) {
                $domains[$report_info['board_id']] = Domain::getDomainFromID($report_info['board_id']);
            }

            $report_domain = $domains[$report_info['board_id']];

            $report_data = array();
            $report_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $content_id = new ContentID($report_info['content_id']);
            $content = $content_id->getInstanceFromID($report_domain);

            if ($content_id->isUpload()) {
                $report_data['content_url'] = $content->getParent()->getURL();
                $report_data['file_url'] = $content->getURL(true);
            } else {
                $report_data['content_url'] = $content->getURL(true);
            }

            $report_data['report_id'] = $report_info['report_id'];
            $report_data['board_uri'] = $report_domain->uri(true);
            $report_data['content_id'] = $report_info['content_id'];
            $report_data['reason'] = $report_info['reason'];
            $report_data['reporter_ip'] = nel_convert_ip_from_storage($report_info['reporter_ip']);
            $report_data['dismiss_url'] = nel_build_router_url(
                [$this->domain->uri(), 'reports', $report_info['report_id'], 'dismiss']);
            $this->render_data['reports_list'][] = $report_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}