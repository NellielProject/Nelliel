<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Filters\FileFilter;
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
        $board_id = $_POST['board_id'] ?? $this->domain->id();
        $domain = Domain::getDomainFromID($board_id, $this->database);
        $this->verifyPermissions($domain, 'perm_manage_file_filters');
        $output_filter = new Filter();
        $hashes = $output_filter->newlinesToArray($_POST['file_hashes']);
        $filter_action = $_POST['filter_action'] ?? '';
        $notes = $_POST['notes'] ?? null;
        $enabled = $_POST['enabled'] ?? 0;

        foreach ($hashes as $hash) {
            $file_filter = new FileFilter($this->database, 0);
            $file_filter->changeData('board_id', $board_id);
            $file_filter->changeData('file_hash', $hash);
            $file_filter->changeData('filter_action', $filter_action);
            $file_filter->changeData('notes', $notes);
            $file_filter->changeData('enabled', $enabled);
            $file_filter->update();
        }

        $this->panel();
    }

    public function editor(int $filter_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_file_filters');
        $output_panel = new OutputPanelFileFilters($this->domain, false);
        $output_panel->edit(['editing' => true, 'filter_id' => $filter_id], false);
    }

    public function update(int $filter_id): void
    {
        $file_filter = new FileFilter($this->database, $filter_id);
        $domain = Domain::getDomainFromID($file_filter->getData('board_id'), $this->database);
        $this->verifyPermissions($domain, 'perm_manage_file_filters');
        $file_filter->changeData('board_id', $_POST['board_id'] ?? $file_filter->getData('board_id'));
        $file_filter->changeData('file_hash', $_POST['file_hash'] ?? $file_filter->getData('file_hash'));
        $file_filter->changeData('filter_action', $_POST['filter_action'] ?? $file_filter->getData('filter_action'));
        $file_filter->changeData('notes', $_POST['notes'] ?? $file_filter->getData('notes'));
        $file_filter->changeData('enabled', $_POST['enabled'] ?? $file_filter->getData('enabled'));
        $file_filter->update();
        $this->panel();
    }

    public function delete(int $filter_id): void
    {
        $file_filter = new FileFilter($this->database, $filter_id);
        $domain = Domain::getDomainFromID($file_filter->getData('board_id'), $this->database);
        $this->verifyPermissions($domain, 'perm_manage_file_filters');
        $file_filter->delete();
        $this->panel();
    }

    public function enable(string $filter_id)
    {
        $file_filter = new FileFilter($this->database, $filter_id);
        $domain = Domain::getDomainFromID($file_filter->getData('board_id'), $this->database);
        $this->verifyPermissions($domain, 'perm_manage_file_filters');
        $file_filter->changeData('enabled', 1);
        $file_filter->update();
        $this->panel();
    }

    public function disable(string $filter_id)
    {
        $file_filter = new FileFilter($this->database, $filter_id);
        $domain = Domain::getDomainFromID($file_filter->getData('board_id'), $this->database);
        $this->verifyPermissions($domain, 'perm_manage_file_filters');
        $file_filter->changeData('enabled', 0);
        $file_filter->update();
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
