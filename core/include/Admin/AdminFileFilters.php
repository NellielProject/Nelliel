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
        $this->id_field = 'filter-id';
        $this->id_column = 'filter_id';
        $this->panel_name = _gettext('File Filters');
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_file_filters_manage');
        $output_panel = new OutputPanelFileFilters($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_file_filters_manage');
        $output_panel = new OutputPanelFileFilters($this->domain, false);
        $output_panel->new(['editing' => false], false);
        $this->outputMain(false);
    }

    public function add(): void
    {
        $board_id = $_POST['board_id'] ?? Domain::GLOBAL;

        if (!Domain::validID($board_id)) {
            $this->outputMain(true); // TODO: Handle properly
            return;
        }

        $domain = Domain::getDomainFromID($board_id, $this->database);
        $this->verifyPermissions($domain, 'perm_file_filters_manage');
        $type = $_POST['hash_type'];
        $notes = $_POST['notes'];
        $output_filter = new Filter();
        $hashes = $output_filter->newlinesToArray($_POST['file_hashes']);

        foreach ($hashes as $hash) {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . $this->data_table .
                '" ("hash_type", "file_hash", "notes", "board_id") VALUES (?, ?, ?, ?)');
            $this->database->executePrepared($prepared, [$type, $hash, $notes, $domain->id()]);
        }

        $this->outputMain(true);
    }

    public function editor(): void
    {
        $this->verifyPermissions($this->domain, 'perm_file_filters_manage');
        $filter_id = $_GET[$this->id_field] ?? 0;
        $output_panel = new OutputPanelFileFilters($this->domain, false);
        $output_panel->edit(['editing' => true, 'filter_id' => $filter_id], false);
        $this->outputMain(false);
    }

    public function update(): void
    {
        $this->verifyPermissions($this->domain, 'perm_file_filters_manage');
        $filter_id = $_GET[$this->id_field] ?? 0;
        $hash_type = $_POST['hash_type'] ?? '';
        $file_hash = $_POST['file_hash'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $board_id = $_POST['board_id'] ?? '';

        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "hash_type" = ?, "file_hash" = ?, "notes" = ?, "board_id" = ? WHERE "filter_id" = ?');
        $this->database->executePrepared($prepared, [$hash_type, $file_hash, $notes, $board_id, $filter_id]);
        $this->outputMain(true);
    }

    public function remove(): void
    {
        $id = $_GET[$this->id_field] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyPermissions($entry_domain, 'perm_file_filters_manage');
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "filter_id" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_file_filters_manage':
                nel_derp(340, _gettext('You are not allowed to manage file filters.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
