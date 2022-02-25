<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Regen;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

class AdminBlotter extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_BLOTTER_TABLE;
        $this->id_field = 'record-id';
        $this->panel_name = _gettext('Blotter');
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_blotter_manage');
        $output_panel = new \Nelliel\Output\OutputPanelBlotter($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator(): void
    {}

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_blotter_manage');
        $text = $_POST['blotter_text'] ?? '';
        $time = time();
        $query = 'INSERT INTO "' . $this->data_table . '" ("time", "text") VALUES (?, ?)';
        $prepared = $this->database->prepare($query);
        $this->database->executePrepared($prepared, [$time, $text]);
        $regen = new Regen();
        $regen->blotter(nel_site_domain());
        $regen->allBoards(true, true);
        $this->outputMain(true);
    }

    public function editor(): void
    {}

    public function update(): void
    {}

    public function remove(): void
    {
        $record_id = $_GET[$this->id_field] ?? 0;
        $this->verifyPermissions($this->domain, 'perm_blotter_manage');
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "record_id" = ?');
        $this->database->executePrepared($prepared, [$record_id]);
        $regen = new Regen();
        $regen->blotter(nel_site_domain());
        $regen->allBoards(true, true);
        $this->outputMain(true);
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_blotter_manage':
                nel_derp(315, _gettext('You do not have permission to manage blotter entries.'));

            default:
                $this->defaultPermissionError();
        }
    }
}