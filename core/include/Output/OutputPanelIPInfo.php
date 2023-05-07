<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\IPNotes;
use Nelliel\Domains\Domain;

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
        $this->setBodyTemplate('panels/ip_info_view');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $id = $parameters['id'] ?? '';
        $panel = '';

        $ip_notes_instance = new IPNotes();
        $ip_notes = $ip_notes_instance->getForIP($this->domain, nel_ip_hash($id));

        $parameters['panel'] = $parameters['panel'] ?? $panel;
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['ip_id'] = $id;
        $this->render_data['can_add_notes'] = $this->session->user()->checkPermission($this->domain, 'add_ip_notes');
        $this->render_data['can_delete_notes'] = $this->session->user()->checkPermission($this->domain,
            'delete_ip_notes');
        $this->render_data['notes_form_action'] = nel_build_router_url([$this->domain->id(), 'ip-notes', $id, 'add']);
        $bgclass = 'row1';
        $this->render_data['ip_notes_list'] = array();

        foreach ($ip_notes as $note) {
            $note_data = array();
            $note_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $note_data['note_id'] = $note['note_id'];
            $note_data['username'] = $note['username'];
            $note_data['time'] = $note['time'];
            $note_data['note'] = $note['notes'];
            $note_data['delete_url'] = nel_build_router_url([$this->domain->id(), 'ip-notes', $note_data['note_id'], 'delete']);
            $this->render_data['notes_list'][] = $note_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}