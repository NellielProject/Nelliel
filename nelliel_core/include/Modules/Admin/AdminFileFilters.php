<?php

declare(strict_types=1);

namespace Nelliel\Modules\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Modules\Account\Session;
use Nelliel\Auth\Authorization;

class AdminFileFilters extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_FILES_FILTERS_TABLE;
        $this->id_field = 'filter-id';
        $this->id_column = 'entry';
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel()
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Modules\Output\OutputPanelFileFilters($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator()
    {
    }

    public function add()
    {
        $this->verifyAction($this->domain);
        $board_id = $board_id = $this->globalIDToNull($_POST['board_id'] ?? '', 'perm_manage_file_filters');
        $type = $_POST['hash_type'];
        $notes = $_POST['file_notes'];
        $output_filter = new \Nelliel\Render\Filter();
        $hashes = $output_filter->newlinesToArray($_POST['file_hashes']);

        foreach ($hashes as $hash)
        {
            $prepared = $this->database->prepare(
                    'INSERT INTO "' . $this->data_table .
                    '" ("hash_type", "file_hash", "file_notes", "board_id") VALUES (?, ?, ?, ?)');
            $this->database->executePrepared($prepared, [$type, pack("H*", $hash), $notes, $board_id]);
        }

        $this->outputMain(true);
    }

    public function editor()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
        $id = $_GET[$this->id_field] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyAction($entry_domain);
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_file_filters'))
        {
            nel_derp(350, _gettext('You do not have access to the File Filters panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_file_filters'))
        {
            nel_derp(351, _gettext('You are not allowed to manage file filters.'));
        }
    }
}
