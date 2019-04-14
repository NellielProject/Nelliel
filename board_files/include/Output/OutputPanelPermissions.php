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

    public function render(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $user = $parameters['user'];
        
        if (!$user->domainPermission($this->domain, 'perm_permissions_access'))
        {
            nel_derp(450, _gettext('You are not allowed to access the Permissions panel.'));
        }
        
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Permissions')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
        $permissions = $this->database->executeFetchAll(
                'SELECT * FROM "' . PERMISSIONS_TABLE . '" ORDER BY "entry" ASC', PDO::FETCH_ASSOC);
        $this->render_data['form_action'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
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
            $this->render_data['permission_list'][] = $permission_data;
        }
        
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/permissions_panel',
                $this->render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}