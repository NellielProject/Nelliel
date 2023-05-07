<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\IPNotes;

class AdminIPNotes extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_IP_NOTES_TABLE;
        $this->id_column = 'note_id';
        $this->panel_name = _gettext('IP Notes');
    }

    public function panel(string $id): void
    {
    }

    public function creator(): void
    {
    }

    public function add(string $id): void
    {
        $this->verifyPermissions($this->domain, 'perm_add_ip_notes');
        $username = $this->session_user->id();
        $ip_address = $_POST['ip_address'] ?? '';
        $hashed_ip_address = nel_ip_hash($ip_address);
        $notes = $_POST['notes'] ?? '';
        $ip_notes = new IPNotes();
        $ip_notes->create($this->domain, $username, $ip_address, $hashed_ip_address, $notes);
    }

    public function editor(): void
    {}

    public function update(): void
    {}

    public function remove(int $note_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_delete_ip_notes');
        $ip_notes = new IPNotes();
        $ip_notes->removeByID($this->domain, $note_id);
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_add_ip_notes':
                nel_derp(445, _gettext('You are not allowed to add IP notes.'));
                break;

            case 'perm_delete_ip_notes':
                nel_derp(446, _gettext('You are not allowed to delete IP notes.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
