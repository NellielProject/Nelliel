<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\IPNote;
use Nelliel\Domains\Domain;
use PDO;

class OutputPanelIPInfo extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/ip_info_main');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('IP Info');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $ip_info = $parameters['ip_info'];
        $access_id = $parameters['access_id'] ?? '';
        $this->render_data['access_id'] = $access_id;
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['hashed_ip_address'] = $ip_info->getInfo('hashed_ip_address');
        $this->render_data['last_activity'] = $ip_info->getInfo('last_activity');
        $this->render_data['has_data'] = $ip_info->infoAvailable();

        if (!is_null($ip_info->getInfo('ip_address'))) {
            $this->render_data['ip_address'] = $ip_info->getInfo('ip_address');
            $this->render_data['can_view_unhashed'] = $this->session->user()->checkPermission($this->domain,
                'view_unhashed_ip');
        }

        $this->render_data['can_add_notes'] = $this->session->user()->checkPermission($this->domain, 'add_ip_notes');
        $this->render_data['can_delete_notes'] = $this->session->user()->checkPermission($this->domain,
            'delete_ip_notes');
        $this->render_data['notes_form_action'] = nel_build_router_url(
            [$this->domain->id(), 'ip-info', $access_id, 'add-note']);
        $bgclass = 'row1';
        $this->render_data['ip_notes_list'] = array();

        $prepared = $this->database->prepare(
            'SELECT "note_id" FROM "' . NEL_IP_NOTES_TABLE . '" WHERE "hashed_ip_address" = :hashed_ip_address');
        $prepared->bindValue(':hashed_ip_address', $ip_info->getInfo('hashed_ip_address'), PDO::PARAM_STR);
        $ip_note_ids = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_COLUMN);

        foreach ($ip_note_ids as $note_id) {
            $ip_note = new IPNote($this->database, (int) $note_id);
            $note_data = array();
            $note_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $note_data['note_id'] = $ip_note->getData('note_id');
            $note_data['username'] = $ip_note->getData('username');
            $note_data['time'] = $ip_note->getData('time');
            $note_data['note'] = $ip_note->getData('notes');
            $note_data['delete_url'] = nel_build_router_url(
                [$this->domain->id(), 'ip-info', $access_id, 'delete-note', $ip_note->getData('note_id')]);
            $this->render_data['notes_list'][] = $note_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}