<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use PDO;

class OutputPanelRoles extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/roles_main');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Roles');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $roles = $this->database->executeFetchAll('SELECT * FROM "' . NEL_ROLES_TABLE . '" ORDER BY "role_level" DESC',
            PDO::FETCH_ASSOC);
        $bgclass = 'row1';
        $authorization = new Authorization($this->database);
        $user_role = $this->session->user()->getDomainRole($this->domain)->id();

        foreach ($roles as $role) {
            $role_data = array();
            $role_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $role_data['role_id'] = $role['role_id'];
            $role_data['role_level'] = $role['role_level'];
            $role_data['role_title'] = $role['role_title'];
            $role_data['capcode'] = $role['capcode'];
            $role_data['can_modify'] = $this->session->user()->isSiteOwner() ||
                ($authorization->roleLevelCheck($user_role, $role['role_id']) &&
                $this->session->user()->checkPermission($this->domain, 'perm_manage_roles'));
            $role_data['edit_url'] = nel_build_router_url([$this->domain->id(), 'roles', $role['role_id'], 'modify']);
            $role_data['delete_url'] = nel_build_router_url([$this->domain->id(), 'roles', $role['role_id'], 'delete']);
            $this->render_data['roles_list'][] = $role_data;
        }

        $this->render_data['new_url'] = nel_build_router_url([$this->domain->id(), 'roles', 'new']);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function new(array $parameters, bool $data_only)
    {
        $parameters['section'] = $parameters['section'] ?? _gettext('New');
        $this->edit($parameters, $data_only);
    }

    public function edit(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/roles_edit');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Roles');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $role_id = $parameters['role_id'] ?? '';
        $authorization = new Authorization($this->domain->database());
        $role = $authorization->getRole($role_id);
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);

        if ($role->empty()) {
            $this->render_data['form_action'] = nel_build_router_url([$this->domain->id(), 'roles', 'new']);
        } else {
            $this->render_data['form_action'] = nel_build_router_url(
                [$this->domain->id(), 'roles', $role->id(), 'modify']);
            $this->render_data['role_id'] = $role->getData('role_id');
            $this->render_data['role_level'] = $role->getData('role_level');
            $this->render_data['role_title'] = $role->getData('role_title');
            $this->render_data['capcode'] = $role->getData('capcode');
        }

        $permissions_list = $this->database->executeFetchAll(
            'SELECT * FROM "' . NEL_PERMISSIONS_TABLE . '" ORDER BY "permission" ASC', PDO::FETCH_ASSOC);

        foreach ($permissions_list as $permission) {
            $permission_data = array();

            if (!empty($role_id)) {
                if ($role->checkPermission($permission['permission'])) {
                    $permission_data['checked'] = 'checked';
                }
            }

            $permission_data['permission'] = $permission['permission'];
            $permission_data['description'] = '(' . $permission['permission'] . ') - ' . $permission['description'];
            $this->render_data['permissions_list'][] = $permission_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}