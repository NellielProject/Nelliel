<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Authorization;
use Nelliel\Domains\Domain;
use PDO;

class OutputPanelUsers extends Output
{
    private $authorization;

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
        $this->authorization = new Authorization($domain->database());
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/users_main');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Users');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $users = $this->database->executeFetchAll('SELECT * FROM "' . NEL_USERS_TABLE . '"', PDO::FETCH_ASSOC);
        $bgclass = 'row1';

        foreach ($users as $user_info) {
            $user_data = array();
            $user_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $user = $this->authorization->getUser($user_info['username']);

            if ($user->empty()) {
                continue;
            }

            $user_data['username'] = $user->id();
            $user_data['display_name'] = $user->getData('display_name');
            $user_data['active'] = $user->active();

            if($user->isSiteOwner() && $user->id() === $this->session->user()->id()) {
                $user_data['can_modify'] = true;
                $user_data['can_delete'] = false;
            } else {
                $user_data['can_modify'] = !$user->isSiteOwner();
                $user_data['can_delete'] = !$user->isSiteOwner();
            }

            $user_data['edit_url'] = nel_build_router_url(
                [$this->domain->uri(), 'users', $user_info['username'], 'modify']);
            $user_data['delete_url'] = nel_build_router_url(
                [$this->domain->uri(), 'users', $user_info['username'], 'delete']);
            $this->render_data['users_list'][] = $user_data;
        }

        $this->render_data['new_url'] = nel_build_router_url([$this->domain->uri(), 'users', 'new']);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function new(array $parameters, bool $data_only)
    {
        $parameters['section'] = $parameters['section'] ?? _gettext('New');
        return $this->edit($parameters, $data_only);
    }

    public function edit(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/users_edit');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Users');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $username = $parameters['username'] ?? '';
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);

        $edit_user = $this->authorization->getUser($username);

        if ($edit_user->empty()) {
            $this->render_data['form_action'] = nel_build_router_url([$this->domain->uri(), 'users', 'new']);
        } else {
            $this->render_data['username'] = $edit_user->getData('username');
            $this->render_data['form_action'] = nel_build_router_url(
                [$this->domain->uri(), 'users', $username, 'modify']);
            $this->render_data['active'] = ($edit_user->active()) ? 'checked' : '';
        }

        if ($edit_user->isSiteOwner()) {
            $this->render_data['is_site_owner'] = true;
        } else {
            $this->render_data['is_site_owner'] = false;
            $domain_list = $this->database->executeFetchAll('SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"',
                PDO::FETCH_COLUMN);
            array_unshift($domain_list, Domain::GLOBAL);
            array_unshift($domain_list, Domain::SITE);
            $query = 'SELECT "role_id", "role_title", "role_level" FROM "' . NEL_ROLES_TABLE .
                '" ORDER BY "role_level" ASC';
            $roles = $this->database->executeFetchAll($query, PDO::FETCH_ASSOC);

            foreach ($domain_list as $domain_id) {
                $domain = Domain::getDomainFromID($domain_id);
                $domain_role_data = array();
                $domain_role_data['domain'] = $domain->uri();
                $domain_role_data['select_name'] = 'domain_role_' . $domain->uri();
                $domain_role_data['select_id'] = 'domain_role_' . $domain->uri();
                $prepared = $this->database->prepare(
                    'SELECT "role_id" FROM "' . NEL_USER_ROLES_TABLE . '" WHERE "username" = ? AND "domain_id" = ?');
                $role_id = $this->database->executePreparedFetch($prepared, [$username, $domain->id()],
                    PDO::FETCH_COLUMN);
                $domain_role_data['roles']['options'][] = ['role_id' => '', 'role_title' => ''];

                foreach ($roles as $role) {
                    $role_options = array();
                    $role_options['option_value'] = $role['role_id'];
                    $role_options['option_label'] = $role['role_title'];

                    if ($role['role_id'] === $role_id) {
                        $role_options['option_selected'] = 'selected';
                    }

                    $domain_role_data['roles']['options'][] = $role_options;
                }

                if ($domain->id() === Domain::SITE) {
                    $domain_role_data['domain_name'] = _gettext('Site');
                    $this->render_data['special_domain_roles'][] = $domain_role_data;
                } else if ($domain->id() === Domain::GLOBAL) {
                    $domain_role_data['domain_name'] = _gettext('Global');
                    $this->render_data['special_domain_roles'][] = $domain_role_data;
                } else {
                    $domain_role_data['domain_name'] = $domain->uri();
                    $this->render_data['domain_roles'][] = $domain_role_data;
                }
            }
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}