<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\BoardEditor;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelManageBoards;

class DispatchManageBoards extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $board_editor = new BoardEditor($this->domain->database());
        $board_uri = trim($_POST['new_board_uri'] ?? '');
        $go_to_panel = true;

        switch ($inputs['section']) {
            case 'new':
                $this->verifyPermissions($this->domain, 'perm_boards_add');
                $custom = array();
                $custom['subdirectories']['source'] = trim($_POST['new_board_src'] ?? '');
                $custom['subdirectories']['preview'] = trim($_POST['new_board_preview'] ?? '');
                $custom['subdirectories']['page'] = trim($_POST['new_board_page'] ?? '');
                $custom['subdirectories']['archive'] = trim($_POST['new_board_archive'] ?? '');

                if ($inputs['method'] === 'POST') {
                    $board_editor->create($board_uri, $custom);
                }

                break;

            case 'delete':
                $this->verifyPermissions($this->domain, 'perm_boards_delete');

                if ($inputs['method'] === 'GET') {
                    $go_to_panel = false;
                    $board_editor->confirmDelete(Domain::getDomainFromID($inputs['id']));
                }

                if ($inputs['method'] === 'POST') {
                    $board_editor->delete(Domain::getDomainFromID($inputs['id']));
                }

                break;

            case 'lock':
                $this->verifyPermissions($this->domain, 'perm_boards_modify');
                $board_editor->lock(Domain::getDomainFromID($inputs['id']));
                break;

            case 'unlock':
                $this->verifyPermissions($this->domain, 'perm_boards_modify');
                $board_editor->unlock(Domain::getDomainFromID($inputs['id']));
                break;

            default:
                ;
        }

        if ($go_to_panel) {
            $this->verifyPermissions($this->domain, 'perm_boards_view');
            $output_panel = new OutputPanelManageBoards($this->domain, false);
            $output_panel->main([], false);
        }
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session->user()->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_boards_view':
                nel_derp(325, __('You are not allowed to manage boards.'), 403);
                break;

            case 'perm_boards_add':
                nel_derp(311, __('You cannot add new boards.'), 403);
                break;

            case 'perm_boards_modify':
                nel_derp(312, __('You cannot modify existing boards.'), 403);
                break;

            case 'perm_boards_delete':
                nel_derp(313, __('You cannot delete existing boards.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}