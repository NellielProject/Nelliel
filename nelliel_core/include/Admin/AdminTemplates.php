<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Auth\Authorization;

class AdminTemplates extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, array $inputs)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->validateUser();
    }

    public function renderPanel()
    {
        $output_panel = new \Nelliel\Render\OutputPanelTemplates($this->domain, false);
        $output_panel->render(['user' => $this->session_user], false);
    }

    public function creator()
    {
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_templates'))
        {
            nel_derp(421, _gettext('You are not allowed to install templates.'));
        }

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
                    'INSERT INTO "' . NEL_TEMPLATES_TABLE . '" ("id", "type", "is_default", "info") VALUES (?, ?, ?, ?)');
            $this->database->executePrepared($prepared, [$template_id, 'template', 0, $info]);
        }

        $this->outputMain(true);
    }

    public function editor()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_templates'))
        {
            nel_derp(422, _gettext('You are not allowed to uninstall templates.'));
        }

        $template_id = $_GET['template-id'];
        $prepared = $this->database->prepare(
                'DELETE FROM "' . NEL_TEMPLATES_TABLE . '" WHERE "id" = ? AND "type" = \'template\'');
        $this->database->executePrepared($prepared, [$template_id]);
        $this->outputMain(true);
    }

    public function makeDefault()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_templates'))
        {
            nel_derp(423, _gettext('You are not allowed to set the default template.'));
        }

        $template_id = $_GET['template-id'];
        $this->database->exec('UPDATE "' . NEL_TEMPLATES_TABLE . '" SET "is_default" = 0');
        $prepared = $this->database->prepare('UPDATE "' . NEL_TEMPLATES_TABLE . '" SET "is_default" = 1 WHERE "id" = ?');
        $this->database->executePrepared($prepared, [$template_id]);
        $this->outputMain(true);
    }

    private function verifyAccess()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_templates'))
        {
            nel_derp(420, _gettext('You are not allowed to access the templates panel.'));
        }
    }
}
