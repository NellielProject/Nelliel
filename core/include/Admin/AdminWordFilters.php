<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

class AdminWordFilters extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_WORD_FILTERS_TABLE;
        $this->id_field = 'wordfilter-id';
        $this->id_column = 'filter_id';
        $this->panel_name = _gettext('Word Filters');
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);

        foreach ($inputs['actions'] as $action) {
            switch ($action) {
                case 'disable':
                    $this->disable();
                    break;

                case 'enable':
                    $this->enable();
                    break;
            }
        }
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_word_filters_manage');
        $output_panel = new \Nelliel\Output\OutputPanelWordfilters($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_word_filters_manage');
        $output_panel = new \Nelliel\Output\OutputPanelWordfilters($this->domain, false);
        $output_panel->new(['editing' => false], false);
        $this->outputMain(false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_word_filters_manage');

        if (is_null($_POST['board_id']) || $_POST['board_id'] === '') {
            $board_id = Domain::GLOBAL;
        } else {
            $board_id = $_POST['board_id'];
        }

        $text_match = $_POST['text_match'] ?? '';
        $replacement = $_POST['replacement'] ?? '';
        $is_regex = $_POST['is_regex'] ?? 0;
        $enabled = $_POST['enabled'] ?? 0;
        $prepared = $this->database->prepare(
            'INSERT INTO "' . $this->data_table .
            '" ("board_id", "text_match", "replacement", "is_regex", "enabled") VALUES (?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$board_id, $text_match, $replacement, $is_regex, $enabled]);
        $this->outputMain(true);
    }

    public function editor(): void
    {
        $filter_id = $_GET['wordfilter-id'] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyPermissions($entry_domain, 'perm_word_filters_manage');
        $output_panel = new \Nelliel\Output\OutputPanelWordfilters($this->domain, false);
        $output_panel->edit(['editing' => true, 'filter_id' => $filter_id], false);
        $this->outputMain(false);
    }

    public function update(): void
    {
        $filter_id = $_GET['wordfilter-id'] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyPermissions($entry_domain, 'perm_word_filters_manage');
        $text_match = $_POST['text_match'] ?? '';
        $replacement = $_POST['replacement'] ?? '';
        $is_regex = $_POST['is_regex'] ?? 0;
        $enabled = $_POST['enabled'] ?? 0;
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table .
            '" SET "board_id" = ?, "text_match" = ?, "replacement" = ? , "is_regex" = ?, "enabled" = ? WHERE "filter_id" = ?');
        $this->database->executePrepared($prepared,
            [$entry_domain->id(), $text_match, $replacement, $is_regex, $enabled, $filter_id]);
        $this->outputMain(true);
    }

    public function remove(): void
    {
        $filter_id = $_GET[$this->id_field] ?? 0;
        $entry_domain = $this->getEntryDomain($filter_id);
        $this->verifyPermissions($entry_domain, 'perm_word_filters_manage');
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "filter_id" = ?');
        $this->database->executePrepared($prepared, [$filter_id]);
        $this->outputMain(true);
    }

    public function enable()
    {
        $filter_id = $_GET[$this->id_field] ?? 0;
        $entry_domain = $this->getEntryDomain($filter_id);
        $this->verifyPermissions($entry_domain, 'perm_word_filters_manage');
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "enabled" = 1 WHERE "filter_id" = ?');
        $this->database->executePrepared($prepared, [$filter_id]);
        $this->outputMain(true);
    }

    public function disable()
    {
        $filter_id = $_GET[$this->id_field] ?? 0;
        $entry_domain = $this->getEntryDomain($filter_id);
        $this->verifyPermissions($entry_domain, 'perm_word_filters_manage');
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "enabled" = 0 WHERE "filter_id" = ?');
        $this->database->executePrepared($prepared, [$filter_id]);
        $this->outputMain(true);
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_word_filters_manage':
                nel_derp(400, _gettext('You are not allowed to manage word filters.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
