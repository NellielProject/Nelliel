<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\Filter;
use Nelliel\Output\OutputPanelFileFilters;

class AdminFileFilters extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_FILE_FILTERS_TABLE;
        $this->id_column = 'filter_id';
        $this->panel_name = _gettext('File Filters');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_file_filters');
        $output_panel = new OutputPanelFileFilters($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_file_filters');
        $output_panel = new OutputPanelFileFilters($this->domain, false);
        $output_panel->new(['editing' => false], false);
    }

    public function add(): void
    {
        $board_id = $_POST['board_id'] ?? Domain::GLOBAL;
        $domain = Domain::getDomainFromID($board_id, $this->database);
        $this->verifyPermissions($domain, 'perm_manage_file_filters');
        $type = $_POST['hash_type'];
        $notes = $_POST['notes'];
        $enabled = 1;
        $output_filter = new Filter();
        $hashes = $output_filter->newlinesToArray($_POST['file_hashes']);

        foreach ($hashes as $hash) {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . $this->data_table .
                '" ("hash_type", "file_hash", "notes", "board_id", "enabled") VALUES (?, ?, ?, ?, ?)');
            $this->database->executePrepared($prepared, [$type, $hash, $notes, $domain->id(), $enabled]);
        }

        $this->panel();
    }

    public function editor(string $filter_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_file_filters');
        $output_panel = new OutputPanelFileFilters($this->domain, false);
        $output_panel->edit(['editing' => true, 'filter_id' => $filter_id], false);
    }

    public function update(string $filter_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_file_filters');
        $hash_type = $_POST['hash_type'] ?? '';
        $file_hash = $_POST['file_hash'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $board_id = $_POST['board_id'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;

        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table .
            '" SET "hash_type" = ?, "file_hash" = ?, "notes" = ?, "board_id" = ?, "enabled" = ? WHERE "filter_id" = ?');
        $this->database->executePrepared($prepared, [$hash_type, $file_hash, $notes, $board_id, $enabled, $filter_id]);
        $this->panel();
    }

    public function delete(string $filter_id): void
    {
        $entry_domain = $this->getEntryDomain($filter_id);
        $this->verifyPermissions($entry_domain, 'perm_manage_file_filters');
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "filter_id" = ?');
        $this->database->executePrepared($prepared, [$filter_id]);
        $this->panel();
    }

    public function enable(string $filter_id)
    {
        $this->verifyPermissions($this->domain, 'perm_manage_file_filters');
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "enabled" = 1 WHERE "filter_id" = ?');
        $this->database->executePrepared($prepared, [$filter_id]);
        $this->panel();
    }

    public function disable(string $filter_id)
    {
        $this->verifyPermissions($this->domain, 'perm_manage_file_filters');
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "enabled" = 0 WHERE "filter_id" = ?');
        $this->database->executePrepared($prepared, [$filter_id]);
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_manage_file_filters':
                nel_derp(340, _gettext('You are not allowed to manage file filters.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
