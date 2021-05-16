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

class AdminDNSBL extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session, array $inputs)
    {
        parent::__construct($authorization, $domain, $session, $inputs);
    }

    public function renderPanel()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelDNSBL($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelDNSBL($this->domain, false);
        $output_panel->new(['editing' => false], false);
        $this->outputMain(false);
    }

    public function add()
    {
        $this->verifyAction();
        $service_domain = $_POST['service_domain'] ?? '';
        $return_codes = $_POST['return_codes'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;
        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_DNSBL_TABLE . '" ("service_domain", "return_codes", "enabled") VALUES (?, ?, ?)');
        $this->database->executePrepared($prepared, [$service_domain, $return_codes, $enabled]);
        $this->outputMain(true);
    }

    public function editor()
    {
        $this->verifyAccess();
        $entry = $_GET['dnsbl-id'] ?? 0;
        $output_panel = new \Nelliel\Render\OutputPanelDNSBL($this->domain, false);
        $output_panel->edit(['editing' => true, 'entry' => $entry], false);
        $this->outputMain(false);
    }

    public function update()
    {
        $this->verifyAction();
        $dnsbl_id = $_GET['dnsbl-id'] ?? 0;
        $service_domain = $_POST['service_domain'] ?? '';
        $return_codes = $_POST['return_codes'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;
        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_DNSBL_TABLE .
                '" SET "service_domain" = ?, "return_codes" = ?, "enabled" = ? WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$service_domain, $return_codes, $enabled, $dnsbl_id]);
        $this->outputMain(true);
    }

    public function remove()
    {
        $this->verifyAction();
        $dnsbl_id = $_GET['dnsbl-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_DNSBL_TABLE . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$dnsbl_id]);
        $this->outputMain(true);
    }

    public function enable()
    {
        $this->verifyAction();
        $dnsbl_id = $_GET['dnsbl-id'];
        $prepared = $this->database->prepare('UPDATE "' . NEL_DNSBL_TABLE . '" SET "enabled" = 1 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$dnsbl_id]);
        $this->outputMain(true);
    }

    public function disable()
    {
        $this->verifyAction();
        $dnsbl_id = $_GET['dnsbl-id'];
        $prepared = $this->database->prepare('UPDATE "' . NEL_DNSBL_TABLE . '" SET "enabled" = 0 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$dnsbl_id]);
        $this->outputMain(true);
    }

    public function makeDefault()
    {
        $this->verifyAction();
    }

    public function verifyAccess()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_dnsbl'))
        {
            nel_derp(480, _gettext('You do not have access to the DNSBL panel.'));
        }
    }

    public function verifyAction()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_dnsbl'))
        {
            nel_derp(481, _gettext('You are not allowed to manage DNSBL entries.'));
        }
    }
}
