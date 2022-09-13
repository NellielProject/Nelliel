<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
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
        $this->setupTimer();
        $this->setBodyTemplate('panels/manage_boards');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Manage Boards');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['form_action'] = nel_build_router_url([$this->domain->id(), 'manage-boards', 'new']);
        $board_ids = $this->database->executeFetchAll('SELECT * FROM "' . NEL_BOARD_DATA_TABLE . '"', PDO::FETCH_COLUMN);
        $bgclass = 'row1';

        foreach ($board_ids as $id) {
            $domain = Domain::getDomainFromID($id, $this->database);
            $board_data = array();
            $board_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $board_data['board_uri'] = $domain->reference('board_uri');
            $board_data['board_url'] = $domain->reference('board_web_path');
            $board_data['board_id'] = $domain->id();
            $board_data['db_prefix'] = $domain->reference('db_prefix');

            if (!$domain->reference('locked')) {
                $board_data['lock_url'] = nel_build_router_url(
                    [$this->domain->id(), 'manage-boards', $domain->id(), 'lock']);
                $board_data['status'] = __('Active');
                $board_data['lock_text'] = __('Lock Board');
            } else {
                $board_data['lock_url'] = nel_build_router_url(
                    [$this->domain->id(), 'manage-boards', $domain->id(), 'unlock']);
                $board_data['status'] = _gettext('Locked');
                $board_data['lock_text'] = _gettext('Unlock Board');
            }

            $board_data['remove_url'] = nel_build_router_url(
                [$this->domain->id(), 'manage-boards', $domain->id(), 'delete']);
            $this->render_data['board_list'][] = $board_data;
        }

        if ($this->domain->setting('allow_custom_subdirectories')) {
            $this->render_data['allow_custom_directories'] = true;
            $this->render_data['alphanumeric_directory_only'] = $this->domain->setting(
                'only_alphanumeric_subdirectories');
            $this->render_data['src_default'] = $this->site_domain->setting('default_source_subdirectory');
            $this->render_data['preview_default'] = $this->site_domain->setting('default_preview_subdirectory');
            $this->render_data['page_default'] = $this->site_domain->setting('default_page_subdirectory');
            $this->render_data['archive_default'] = $this->site_domain->setting('default_archive_subdirectory');
        }

        $this->render_data['alphanumeric_uri_only'] = $this->domain->setting('only_alphanumeric_board_ids');
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function removeWarning(array $parameters, bool $data_only)
    {
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Manage Boards');
        $parameters['section'] = $parameters['section'] ?? _gettext('Remove');
        $parameters['is_manage'] = true;
        $board_id = $parameters['board_id'];
        $board = new DomainBoard($board_id, $this->database);
        $messages[] = sprintf(_gettext('You are about to delete the board: %s'), $board->reference('board_uri'));
        $messages[] = _gettext(
            'This will wipe out all posts, settings, files, everything. There is no undo or recovery.');
        $messages[] = _gettext('Are you sure?');
        $link['text'] = _gettext('NOPE. Do not delete the board.');
        $link['url'] = nel_build_router_url([$this->domain->id(), 'manage-boards']);
        $link2['text'] = _gettext('Confirmed. Delete the board.');
        $link2['url'] = nel_build_router_url([$this->domain->id(), 'manage-boards', $board_id, 'remove-confirmed']);
        $parameters['extra_url_break'] = true;
        $parameters['page_title'] = $this->domain->reference('title');
        $output_interstitial = new OutputInterstitial($this->domain, $this->write_mode);
        echo $output_interstitial->render($parameters, $data_only, $messages, [$link, $link2]);
    }
}