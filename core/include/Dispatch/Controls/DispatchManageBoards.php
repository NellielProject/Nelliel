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
        $site_domain = nel_get_cached_domain(Domain::SITE);
        $board_editor = new BoardEditor($this->domain->database());
        $board_uri = trim($_POST['board_uri'] ?? '');
        $go_to_panel = true;

        switch ($inputs['section']) {
            case 'new':
                $this->verifyPermissions($this->domain, 'perm_boards_add');

                if ($inputs['method'] === 'GET') {
                    $output_panel = new OutputPanelManageBoards($this->domain, false);
                    $output_panel->new([], false);
                    $go_to_panel = false;
                }

                if ($inputs['method'] === 'POST') {
                    $custom = array();
                    $custom['subdirectories']['source'] = trim(
                        $_POST['source_directory'] ?? $site_domain->setting('default_source_subdirectory'));
                    $custom['subdirectories']['preview'] = trim(
                        $_POST['preview_directory'] ?? $site_domain->setting('default_preview_subdirectory'));
                    $custom['subdirectories']['page'] = trim(
                        $_POST['page_directory'] ?? $site_domain->setting('default_page_subdirectory'));
                    $custom['subdirectories']['archive'] = trim(
                        $_POST['archive_directory'] ?? $site_domain->setting('default_archive_subdirectory'));
                    $success = $board_editor->create($board_uri, $custom);

                    if (!$success) {
                        nel_derp(101, __('Could not create the board as requested.'));
                    }
                }

                break;

            case 'modify':
                $this->verifyPermissions($this->domain, 'perm_boards_modify');
                $board = Domain::getDomainFromID($inputs['id']);
                $board_id = $board->id();

                if ($inputs['method'] === 'GET') {
                    $output_panel = new OutputPanelManageBoards($this->domain, false);
                    $output_panel->edit(['board' => $board], false);
                    $go_to_panel = false;
                }

                if ($inputs['method'] === 'POST') {
                    $subdirectories = array();
                    $subdirectories['source'] = trim($_POST['source_directory'] ?? '');
                    $subdirectories['preview'] = trim($_POST['preview_directory'] ?? '');
                    $subdirectories['page'] = trim($_POST['page_directory'] ?? '');
                    $subdirectories['archive'] = trim($_POST['archive_directory'] ?? '');

                    $board_editor->updateURI($board, $board_uri);
                    $board = Domain::getDomainFromID($board_id);
                    $board_editor->updateSubdirectories($board, $subdirectories);
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