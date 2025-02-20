<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\IPInfo;
use Nelliel\IPNote;
use Nelliel\Account\Session;
use Nelliel\Account\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelIPInfo;

class DispatchIPInfo extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $ip = rawurldecode($inputs['ip'] ?? '');
        $note_id = intval($inputs['note-id'] ?? 0);
        $ip_info = new IPInfo($ip, false);
        $forward_url = false;

        if (nel_is_unhashed_ip($ip) && !$this->session->user()->checkPermission($this->domain, 'perm_view_unhashed_ip')) {
            nel_derp(440, _gettext('You cannot access info for unhashed IPs.'), 403);
        }

        switch ($inputs['section']) {
            case 'view':
                if (!$this->session->user()->checkPermission($this->domain, 'perm_view_ip_info')) {
                    nel_derp(441, _gettext('You are not allowed to view IP info.'), 403);
                }

                $output_panel = new OutputPanelIPInfo($this->domain, false);
                $output_panel->render(['access_id' => $ip, 'ip_info' => $ip_info], false);
                break;

            case 'add-note':
                if (!$this->session->user()->checkPermission($this->domain, 'perm_add_ip_notes')) {
                    nel_derp(442, _gettext('You are not allowed to add IP notes.'), 403);
                }

                $ip_address = $_POST['ip_address'] ?? '';
                $notes = $_POST['notes'] ?? '';
                $ip_note = new IPNote($this->domain->database());
                $ip_note->update($this->session->user()->id(), $ip_address, $notes);
                $forward_url = true;
                break;

            case 'delete-note':
                if (!$this->session->user()->checkPermission($this->domain, 'perm_delete_ip_notes')) {
                    nel_derp(443, _gettext('You are not allowed to delete IP notes.'), 403);
                }

                $ip_note = new IPNote($this->domain->database(), $note_id);
                $ip_note->delete();
                $forward_url = true;
                break;
        }

        if ($forward_url) {
            $return_url = nel_build_router_url([$this->domain->id(), 'ip-info', $ip, 'view']);
            header('Location: ' . $return_url, true);
        }
    }
}