<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelNoticeboard extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/noticeboard');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Noticeboard');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $notices = $this->database->executeFetchAll('SELECT * FROM "' . NEL_NOTICEBOARD_TABLE . '" ORDER BY "time" ASC',
            PDO::FETCH_ASSOC);
        $bgclass = 'row1';
        $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
            http_build_query(['module' => 'admin', 'section' => 'noticeboard', 'actions' => 'add']);

        foreach ($notices as $notice) {
            $notice_info = array();
            $notice_info['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $notice_info['name'] = $notice['username'];
            $notice_info['message'] = $notice['message'];
            $notice_info['subject'] = $notice['subject'];
            $notice_info['time'] = date('Y/m/d (D) H:i:s', intval($notice['time']));
            $notice_info['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'noticeboard', 'actions' => 'remove',
                        'notice-id' => $notice['notice_id']]);
            $this->render_data['notices'][] = $notice_info;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}