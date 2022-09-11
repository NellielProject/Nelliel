<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelNoticeboard;

class AdminNoticeboard extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_NOTICEBOARD_TABLE;
        $this->id_field = 'notice-id';
        $this->id_column = 'notice_id';
        $this->panel_name = _gettext('Noticeboard');
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_noticeboard_view');
        $output_panel = new OutputPanelNoticeboard($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator(): void
    {}

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_noticeboard_post');
        $notice_info = array();
        $notice_info['username'] = $this->session_user->id();
        $notice_info['name'] = $_POST['name'] ?? '';

        if ($notice_info['name'] === '' || !$this->session_user->checkPermission($this->domain, 'perm_custom_name')) {
            $notice_info['username'] = $this->session_user->id();
        }

        $notice_info['subject'] = $_POST['subject'] ?? null;
        $notice_info['time'] = time();
        $notice_info['message'] = $_POST['message'] ?? null;
        $query = 'INSERT INTO "' . $this->data_table . '" ("username", "time", "subject", "message") VALUES (?, ?, ?, ?)';
        $prepared = $this->database->prepare($query);
        $this->database->executePrepared($prepared,
            [$notice_info['username'], $notice_info['time'], $notice_info['subject'], $notice_info['message']]);
        $this->panel();
    }

    public function editor(): void
    {}

    public function update(): void
    {}

    public function delete(string $notice_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_noticeboard_delete');
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "notice_id" = ?');
        $this->database->executePrepared($prepared, [$notice_id]);
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_noticeboard_view':
                nel_derp(430, _gettext('You are not allowed to manage the noticeboard'));
                break;

            case 'perm_noticeboard_post':
                nel_derp(431, _gettext('You are not allowed to post notices.'));
                break;

            case 'perm_noticeboard_delete':
                nel_derp(432, _gettext('You are not allowed to delete notices.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
