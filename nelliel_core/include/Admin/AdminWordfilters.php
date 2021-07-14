<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use PDO;

class AdminWordfilters extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_WORD_FILTERS_TABLE;
    }

    public function renderPanel()
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Render\OutputPanelWordfilters($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator()
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Render\OutputPanelWordfilters($this->domain, false);
        $output_panel->new(['editing' => false], false);
        $this->outputMain(false);
    }

    public function add()
    {
        $this->verifyAction($this->domain);
        $board_id = nel_convert_global_ID($_POST['board_id'] ?? '', true);
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

    public function editor()
    {
        $id = $_GET['wordfilter-id'] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyAccess($entry_domain);
        $output_panel = new \Nelliel\Render\OutputPanelWordfilters($this->domain, false);
        $output_panel->edit(['editing' => true, 'entry' => $id], false);
        $this->outputMain(false);
    }

    public function update()
    {
        $id = $_GET['wordfilter-id'] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyAction($entry_domain);
        $text_match = $_POST['text_match'] ?? '';
        $replacement = $_POST['replacement'] ?? '';
        $is_regex = $_POST['is_regex'] ?? 0;
        $enabled = $_POST['enabled'] ?? 0;
        $prepared = $this->database->prepare(
                'UPDATE "' . $this->data_table .
                '" SET "board_id" = ?, "text_match" = ?, "replacement" = ? , "is_regex" = ?, "enabled" = ? WHERE "entry" = ?');
        $this->database->executePrepared($prepared,
                [$entry_domain->id(), $text_match, $replacement, $is_regex, $enabled, $id]);
        $this->outputMain(true);
    }

    public function remove()
    {
        $id = $_GET['wordfilter-id'] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyAction($entry_domain);
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    public function enable()
    {
        $id = $_GET['wordfilter-id'] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyAction($entry_domain);
        $prepared = $this->database->prepare(
                'UPDATE "' . $this->data_table . '" SET "enabled" = 1 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    public function disable()
    {
        $id = $_GET['wordfilter-id'] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyAction($entry_domain);
        $prepared = $this->database->prepare(
                'UPDATE "' . $this->data_table . '" SET "enabled" = 0 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    public function makeDefault()
    {
        $this->verifyAction($this->domain);
    }

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($domain, 'perm_manage_wordfilters'))
        {
            nel_derp(490, _gettext('You do not have access to the Wordfilters panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($domain, 'perm_manage_wordfilters'))
        {
            nel_derp(491, _gettext('You are not allowed to manage wordfilters.'));
        }
    }
}
