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

class AdminStyles extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_ASSETS_TABLE;
        $this->id_field = 'style-id';
        $this->id_column = 'asset_id';
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Output\OutputPanelStyles($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator(): void
    {
        $this->verifyAccess($this->domain);
    }

    public function add(): void
    {
        $id = $_GET[$this->id_field] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyAction($entry_domain);
        $style_inis = $this->domain->frontEndData()->getStyleInis();

        foreach ($style_inis as $ini)
        {
            if ($ini['id'] === $id)
            {
                $info = json_encode($ini);
            }
        }

        $prepared = $this->database->prepare(
                'INSERT INTO "' . $this->data_table . '" ("asset_id", "type", "is_default", "info") VALUES (?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$id, 'style', 0, $info]);
        $this->outputMain(true);
    }

    public function editor(): void
    {
    }

    public function update(): void
    {
    }

    public function remove(): void
    {
        $id = $_GET[$this->id_field] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyAction($entry_domain);
        $prepared = $this->database->prepare(
                'DELETE FROM "' . $this->data_table . '" WHERE "asset_id" = ? AND "type" = \'style\'');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    public function makeDefault()
    {
        $id = $_GET[$this->id_field] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyAction($entry_domain);
        $this->database->exec('UPDATE "' . $this->data_table . '" SET "is_default" = 0 WHERE "type" = \'style\'');
        $prepared = $this->database->prepare(
                'UPDATE "' . $this->data_table . '" SET "is_default" = 1 WHERE "asset_id" = ? AND "type" = \'style\'');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_styles'))
        {
            nel_derp(410, _gettext('You do not have access to the Styles panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_styles'))
        {
            nel_derp(411, _gettext('You are not allowed to manage styles.'));
        }
    }
}
