<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelManageBoards extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/manage_boards_main');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Manage Boards');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $board_ids = $this->database->executeFetchAll('SELECT * FROM "' . NEL_BOARD_DATA_TABLE . '"', PDO::FETCH_COLUMN);
        $this->render_data['new_url'] = nel_build_router_url([$this->domain->uri(), 'manage-boards', 'new']);
        $bgclass = 'row1';

        foreach ($board_ids as $id) {
            $domain = Domain::getDomainFromID($id);
            $board_data = array();
            $board_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $board_data['board_uri'] = $domain->uri();
            $board_data['board_url'] = $domain->reference('board_web_path');
            $board_data['board_id'] = $domain->id();
            $board_data['db_prefix'] = $domain->reference('db_prefix');

            if (!$domain->reference('locked')) {
                $board_data['lock_unlock_url'] = nel_build_router_url(
                    [$this->domain->uri(), 'manage-boards', $domain->uri(), 'lock']);
                $board_data['status'] = __('Active');
                $board_data['lock_unlock_text'] = __('Lock');
            } else {
                $board_data['lock_unlock_url'] = nel_build_router_url(
                    [$this->domain->uri(), 'manage-boards', $domain->uri(), 'unlock']);
                $board_data['status'] = _gettext('Locked');
                $board_data['lock_unlock_text'] = _gettext('Unlock');
            }

            $board_data['edit_url'] = nel_build_router_url(
                [$this->domain->uri(), 'manage-boards', $domain->uri(), 'modify']);
            $board_data['delete_url'] = nel_build_router_url(
                [$this->domain->uri(), 'manage-boards', $domain->uri(), 'delete']);
            $this->render_data['board_list'][] = $board_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function new(array $parameters, bool $data_only)
    {
        $parameters['section'] = $parameters['section'] ?? __('New');
        $parameters['editing'] = false;
        return $this->edit($parameters, $data_only);
    }

    public function edit(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/manage_boards_edit');
        $parameters['panel'] = $parameters['panel'] ?? __('Manage Boards');
        $parameters['section'] = $parameters['section'] ?? __('Edit');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $editing = $parameters['editing'] ?? true;
        $board = $parameters['board'] ?? null;

        if ($editing) {
            $this->render_data['form_action'] = nel_build_router_url(
                [$this->domain->uri(), 'manage-boards', $board->uri(), 'modify']);
            $this->render_data['board_uri'] = $board->uri(true);
            $this->render_data['source_directory'] = $board->reference('source_directory');
            $this->render_data['preview_directory'] = $board->reference('preview_directory');
            $this->render_data['page_directory'] = $board->reference('page_directory');
            $this->render_data['archive_directory'] = $board->reference('archive_directory');
        } else {
            $this->render_data['form_action'] = nel_build_router_url([$this->domain->uri(), 'manage-boards', 'new']);
            $this->render_data['source_directory'] = $this->site_domain->setting('default_source_subdirectory');
            $this->render_data['preview_directory'] = $this->site_domain->setting('default_preview_subdirectory');
            $this->render_data['page_directory'] = $this->site_domain->setting('default_page_subdirectory');
            $this->render_data['archive_directory'] = $this->site_domain->setting('default_archive_subdirectory');
        }

        if ($this->domain->setting('allow_custom_subdirectories')) {
            $this->render_data['allow_custom_directories'] = true;
            $this->render_data['alphanumeric_directory_only'] = $this->site_domain->setting(
                'only_alphanumeric_subdirectories');
        }

        $this->render_data['alphanumeric_uri_only'] = $this->site_domain->setting('only_alphanumeric_board_ids');

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}