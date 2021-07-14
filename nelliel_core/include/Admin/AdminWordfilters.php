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

class AdminWordfilters extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session, array $inputs)
    {
        parent::__construct($authorization, $domain, $session, $inputs);
    }

    public function renderPanel()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelWordfilters($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelWordfilters($this->domain, false);
        $output_panel->new(['editing' => false], false);
        $this->outputMain(false);
    }

    public function add()
    {
        $this->verifyAction();
        $board_id = nel_filter_global_ID($_POST['board_id'] ?? '', 'perm_manage_wordfilters', $this->session_user);
        $text_match = $_POST['text_match'] ?? '';
        $replacement = $_POST['replacement'] ?? '';
        $is_regex = $_POST['is_regex'] ?? 0;
        $enabled = $_POST['enabled'] ?? 0;
        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_WORD_FILTERS_TABLE .
                '" ("board_id", "text_match", "replacement", "is_regex", "enabled") VALUES (?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$board_id, $text_match, $replacement, $is_regex, $enabled]);
        $this->outputMain(true);
    }

    public function editor()
    {
        $this->verifyAccess();
        $entry = $_GET['wordfilter-id'] ?? 0;
        $output_panel = new \Nelliel\Render\OutputPanelWordfilters($this->domain, false);
        $output_panel->edit(['editing' => true, 'entry' => $entry], false);
        $this->outputMain(false);
    }

    public function update()
    {
        $this->verifyAction();
        $wordfilter_id = $_GET['wordfilter-id'] ?? 0;
        $board_id = nel_filter_global_ID($_POST['board_id'] ?? '', 'perm_manage_wordfilters', $this->session_user);
        $text_match = $_POST['text_match'] ?? '';
        $replacement = $_POST['replacement'] ?? '';
        $is_regex = $_POST['is_regex'] ?? 0;
        $enabled = $_POST['enabled'] ?? 0;
        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_WORD_FILTERS_TABLE .
                '" SET "board_id" = ?, "text_match" = ?, "replacement" = ? , "is_regex" = ?, "enabled" = ? WHERE "entry" = ?');
        $this->database->executePrepared($prepared,
                [$board_id, $text_match, $replacement, $is_regex, $enabled, $wordfilter_id]);
        $this->outputMain(true);
    }

    public function remove()
    {
        $this->verifyAction();
        $wordfilter_id = $_GET['wordfilter-id'] ?? 0;
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_WORD_FILTERS_TABLE . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$wordfilter_id]);
        $this->outputMain(true);
    }

    public function enable()
    {
        $this->verifyAction();
        $wordfilter_id = $_GET['wordfilter-id'] ?? 0;
        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_WORD_FILTERS_TABLE . '" SET "enabled" = 1 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$wordfilter_id]);
        $this->outputMain(true);
    }

    public function disable()
    {
        $this->verifyAction();
        $wordfilter_id = $_GET['wordfilter-id'] ?? 0;
        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_WORD_FILTERS_TABLE . '" SET "enabled" = 0 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$wordfilter_id]);
        $this->outputMain(true);
    }

    public function makeDefault()
    {
        $this->verifyAction();
    }

    public function verifyAccess()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_wordfilters'))
        {
            nel_derp(490, _gettext('You do not have access to the Wordfilters panel.'));
        }
    }

    public function verifyAction()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_wordfilters'))
        {
            nel_derp(491, _gettext('You are not allowed to manage wordfilters.'));
        }
    }
}
