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
        $this->setBodyTemplate('panels/noticeboard');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Noticeboard');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $notices = $this->database->executeFetchAll('SELECT * FROM "' . NEL_NOTICEBOARD_TABLE . '" ORDER BY "time" ASC',
            PDO::FETCH_ASSOC);
        $bgclass = 'row1';
        $this->render_data['form_action'] = nel_build_router_url([$this->domain->uri(), 'noticeboard', 'new']);
        $this->render_data['can_post'] = $this->session->user()->checkPermission($this->domain, 'perm_noticeboard_post');
        $this->render_data['can_delete'] = $this->session->user()->checkPermission($this->domain,
            'perm_noticeboard_delete');

        foreach ($notices as $notice) {
            $notice_info = array();
            $notice_info['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $notice_info['name'] = $notice['username'];
            $notice_info['message'] = $notice['message'];
            $notice_info['subject'] = $notice['subject'];
            $notice_info['time'] = $this->domain->domainDateTime(intval($notice['time']))->format(
                $this->site_domain->setting('control_panel_list_time_format'));
            $notice_info['delete_url'] = nel_build_router_url(
                [$this->domain->uri(), 'noticeboard', $notice['notice_id'], 'delete']);
            $this->render_data['notices'][] = $notice_info;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function viewNotice(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('pieces/notice');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Noticeboard');
        $parameters['section'] = $parameters['section'] ?? _gettext('View');
        $notice_id = $parameters['notice_id'] ?? 0;
        $prepared = $this->database->prepare(
            'SELECT * FROM "' . NEL_NOTICEBOARD_TABLE . '" WHERE "notice_id" = ?');
        $prepared->bindValue(1, $notice_id, PDO::PARAM_INT);
        $notice = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);

        if (!is_array($notice)) {
            return;
        }

        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['name'] = $notice['username'];
        $this->render_data['message'] = $notice['message'];
        $this->render_data['subject'] = $notice['subject'];
        $this->render_data['time'] = $this->domain->domainDateTime(intval($notice['time']))->format(
            $this->site_domain->setting('control_panel_list_time_format'));
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}