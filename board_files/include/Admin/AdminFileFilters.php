<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

class AdminFileFilters extends AdminHandler
{

    function __construct(Authorization $authorization, Domain $domain)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
    }

    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Session(true);
        $user = $session->sessionUser();

        if ($inputs['action'] === 'add')
        {
            $this->add($user);
        }
        else if ($inputs['action'] == 'remove')
        {
            $this->remove($user);
        }

        $this->renderPanel($user);
    }

    public function renderPanel($user)
    {
        $output_panel = new \Nelliel\Output\OutputPanelFileFilters($this->domain);
        $output_panel->render(['user' => $user], false);
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
        if (!$user->domainPermission($this->domain, 'perm_file_filters_modify'))
        {
            nel_derp(341, _gettext('You are not allowed to modify file filters.'));
        }

        $type = $_POST['hash_type'];
        $notes = $_POST['file_notes'];
        $board_id = $_POST['board_id'];
        $output_filter = new \Nelliel\OutputFilter();
        $hashes = $output_filter->newlinesToArray($_POST['file_hashes']);

        foreach ($hashes as $hash)
        {
            $prepared = $this->database->prepare(
                    'INSERT INTO "' . FILE_FILTERS_TABLE .
                    '" ("hash_type", "file_hash", "file_notes", "board_id") VALUES (?, ?, ?, ?)');
            $this->database->executePrepared($prepared, [$type, pack("H*", $hash), $notes, $board_id]);
        }

        $this->renderPanel($user);
    }

    public function editor($user)
    {
    }

    public function update($user)
    {
    }

    public function remove($user)
    {
        if (!$user->domainPermission($this->domain, 'perm_file_filters_modify'))
        {
            nel_derp(341, _gettext('You are not allowed to modify file filters.'));
        }

        $filter_id = $_GET['filter-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . FILE_FILTERS_TABLE . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$filter_id]);
        $this->renderPanel($user);
    }
}
