<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;

class AdminDNSBL extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_DNSBL_TABLE;
        $this->id_field = 'dnsbl-id';
        $this->id_column = 'entry';
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);

        foreach ($inputs['actions'] as $action)
        {
            switch ($action)
            {
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
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Output\OutputPanelDNSBL($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Output\OutputPanelDNSBL($this->domain, false);
        $output_panel->new(['editing' => false], false);
        $this->outputMain(false);
    }

    public function add(): void
    {
        $this->verifyAction($this->domain);
        $service_domain = $_POST['service_domain'] ?? '';
        $return_codes = $_POST['return_codes'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;
        $prepared = $this->database->prepare(
                'INSERT INTO "' . $this->data_table . '" ("service_domain", "return_codes", "enabled") VALUES (?, ?, ?)');
        $this->database->executePrepared($prepared, [$service_domain, $return_codes, $enabled]);
        $this->outputMain(true);
    }

    public function editor(): void
    {
        $this->verifyAccess($this->domain);
        $entry = $_GET['dnsbl-id'] ?? 0;
        $output_panel = new \Nelliel\Output\OutputPanelDNSBL($this->domain, false);
        $output_panel->edit(['editing' => true, 'entry' => $entry], false);
        $this->outputMain(false);
    }

    public function update(): void
    {
        $this->verifyAction($this->domain);
        $dnsbl_id = $_GET['dnsbl-id'] ?? 0;
        $service_domain = $_POST['service_domain'] ?? '';
        $return_codes = $_POST['return_codes'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;
        $prepared = $this->database->prepare(
                'UPDATE "' . $this->data_table .
                '" SET "service_domain" = ?, "return_codes" = ?, "enabled" = ? WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$service_domain, $return_codes, $enabled, $dnsbl_id]);
        $this->outputMain(true);
    }

    public function remove(): void
    {
        $id = $_GET[$this->id_field] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyAction($entry_domain);
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    public function enable()
    {
        $id = $_GET[$this->id_field] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyAction($entry_domain);
        $prepared = $this->database->prepare('UPDATE "' . $this->data_table . '" SET "enabled" = 1 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    public function disable()
    {
        $id = $_GET[$this->id_field] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyAction($entry_domain);
        $prepared = $this->database->prepare('UPDATE "' . $this->data_table . '" SET "enabled" = 0 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_dnsbl'))
        {
            nel_derp(480, _gettext('You do not have access to the DNSBL panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_dnsbl'))
        {
            nel_derp(481, _gettext('You are not allowed to manage DNSBL entries.'));
        }
    }
}
