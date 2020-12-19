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

    function __construct(Domain $domain, bool $write_mode)
    {
        $this->domain = $domain;
        $this->write_mode = $write_mode;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Permissions')];
        $this->render_data['header'] = $output_header->general(['manage_headers' => $manage_headers], true);
        $permissions = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_PERMISSIONS_TABLE . '" ORDER BY "entry" ASC', PDO::FETCH_ASSOC);
        $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(['module' => 'admin', 'section' => 'permissions', 'actions' => 'add']);
        $bgclass = 'row1';

        foreach ($permissions as $permission)
        {
            $permission_data = array();
            $permission_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $permission_data['permission'] = $permission['permission'];
            $permission_data['description'] = _gettext($permission['description']);
            $permission_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'permissions', 'actions' => 'remove',
                                'permission' => $permission['permission']]);
            $this->render_data['permission_list'][] = $permission_data;
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/permissions_panel',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}