<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelPermissions extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_permissions_access'))
        {
            nel_derp(450, _gettext('You are not allowed to access the Permissions panel.'));
        }

        $this->startTimer();
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Permissions')];
        $this->render_core->appendToOutput(
                $output_header->render(['header_type' => 'general', 'dotdot' => '', 'manage_render' => true, 'extra_data' => $extra_data]));
        $permissions = $this->database->executeFetchAll(
                'SELECT * FROM "' . PERMISSIONS_TABLE . '" ORDER BY "entry" ASC', PDO::FETCH_ASSOC);
        $render_input['form_action'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                ['module' => 'permissions', 'action' => 'add']);
        $bgclass = 'row1';

        foreach ($permissions as $permission)
        {
            $permission_data = array();
            $permission_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $permission_data['permission'] = $permission['permission'];
            $permission_data['description'] = $permission['description'];
            $permission_data['remove_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'permissions', 'action' => 'remove', 'permission' => $permission['permission']]);
            $render_input['permission_list'][] = $permission_data;
        }

        $this->render_core->appendToOutput(
                $this->render_core->renderFromTemplateFile('management/panels/permissions_panel', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        echo $this->render_core->getOutput();
        nel_clean_exit();
    }
}