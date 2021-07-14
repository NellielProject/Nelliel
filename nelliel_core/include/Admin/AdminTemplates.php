<?php

declare(strict_types=1);

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;

class AdminTemplates extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_TEMPLATES_TABLE;
        $this->id_field = 'template-id';
        $this->id_column = 'template_id';
    }

    public function renderPanel()
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Render\OutputPanelTemplates($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator()
    {
        $this->verifyAccess($this->domain);
    }

    public function add()
    {
        $this->verifyAction($this->domain);
        $template_id = $_GET['template-id'];
        $template_inis = $this->domain->frontEndData()->getTemplateInis();
        $info = '';

        foreach ($template_inis as $ini)
        {
            if ($ini['id'] === $template_id)
            {
                $info = json_encode($ini);
            }
        }

        if ($info !== '')
        {
            $prepared = $this->database->prepare(
                    'INSERT INTO "' . $this->data_table .
                    '" ("template_id", "type", "is_default", "info") VALUES (?, ?, ?, ?)');
            $this->database->executePrepared($prepared, [$template_id, 'template', 0, $info]);
        }

        $this->outputMain(true);
    }

    public function editor()
    {
        $this->verifyAccess($this->domain);
    }

    public function update()
    {
        $this->verifyAction($this->domain);
    }

    public function enable()
    {
        $this->verifyAction($this->domain);
    }

    public function disable()
    {
        $this->verifyAction($this->domain);
    }

    public function makeDefault()
    {
        $this->verifyAction($this->domain);
        $template_id = $_GET['template-id'];
        $this->database->exec('UPDATE "' . $this->data_table . '" SET "is_default" = 0');
        $prepared = $this->database->prepare(
                'UPDATE "' . $this->data_table . '" SET "is_default" = 1 WHERE "template_id" = ?');
        $this->database->executePrepared($prepared, [$template_id]);
        $this->outputMain(true);
    }

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_templates'))
        {
            nel_derp(390, _gettext('You do not have access to the Templates panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_templates'))
        {
            nel_derp(391, _gettext('You are not allowed to manage Templates.'));
        }
    }
}
