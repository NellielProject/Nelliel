<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Authorization;
use Nelliel\Domains\Domain;
use PDO;

class OutputBoardlist extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('boardlist');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Boardlist');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $authorization = new Authorization($this->database);
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_USER_ROLES_TABLE . '" WHERE "username" = ?');
        $user_roles = $this->database->executePreparedFetchAll($prepared, [$this->session->user()->id()],
            PDO::FETCH_ASSOC);
        $boards = $this->database->executeFetchAll('SELECT * FROM "' . NEL_BOARD_DATA_TABLE . '"', PDO::FETCH_ASSOC);

        $roles_list = array();
        $roles = $this->database->executeFetchAll(
            'SELECT "role_id", "role_title", "role_level" FROM "' . NEL_ROLES_TABLE . '"', PDO::FETCH_ASSOC);

        foreach ($roles as $role) {
            $roles_list[$role['role_id']] = $role;
        }

        $user_roles_list = array();
        $has_global = false;
        $global_role_id = '';
        $global_role_title = '';

        foreach ($user_roles as $user_role) {
            if (!isset($roles_list[$user_role['role_id']]) || $roles_list[$user_role['role_id']]['role_level'] <= 0) {
                continue;
            }

            $user_roles_list[$user_role['domain_id']]['role_id'] = $user_role['role_id'];
            $user_roles_list[$user_role['domain_id']]['role_title'] = $roles_list[$user_role['role_id']]['role_title'];

            if ($user_role['domain_id'] === Domain::GLOBAL) {
                $has_global = true;
                $global_role_id = $user_role['role_id'];
                $global_role_title = $roles_list[$user_role['role_id']]['role_title'];
            }
        }

        foreach ($boards as $board) {
            if ($board['board_id'] === Domain::SITE) {
                continue;
            }

            if (!isset($user_roles_list[$board['board_id']]) && !$this->session->user()->isSiteOwner() && !$has_global) {
                continue;
            }

            $board_domain = Domain::getDomainFromID($board['board_id']);

            if(!$board_domain->exists()) {
                continue;
            }

            $board_data['board_url'] = nel_build_router_url([$board_domain->uri(), 'main-panel']);
            $board_data['board_uri'] = $board_domain->uri(true, true);
            $global_level_lower = $authorization->roleLevelCheck(
                $user_roles_list[$board['board_id']]['role_title'] ?? '', $global_role_id);

            if ($this->session->user()->isSiteOwner()) {
                $board_data['board_role'] = _gettext('Site Owner');
            } else if (!isset($user_roles_list[$board['board_id']]) || !$global_level_lower) {
                $board_data['board_role'] = ($has_global) ? $global_role_title : '';
            } else {
                $board_data['board_role'] = $user_roles_list[$board['board_id']]['role_title'];
            }

            $this->render_data['board_list'][] = $board_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}