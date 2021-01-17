<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

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
        $this->setupTimer();
        $this->setBodyTemplate('panels/manage_boards');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Manage Boards');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(['module' => 'admin', 'section' => 'manage-boards', 'actions' => 'add']);
        $board_data = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_BOARD_DATA_TABLE . '" ORDER BY "board_id" DESC', PDO::FETCH_ASSOC);
        $bgclass = 'row1';

        foreach ($board_data as $board_info)
        {
            $board_data = array();
            $board_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $board_data['board_uri'] = $board_info['board_id'];
            $board_data['board_url'] = NEL_BASE_WEB_PATH . $board_info['board_id'] . '/';
            $board_data['db_prefix'] = $board_info['db_prefix'];

            if ($board_info['locked'] == 0)
            {
                $board_data['lock_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                        http_build_query(
                                ['module' => 'admin', 'section' => 'manage-boards',
                                    'board-id' => $board_info['board_id'], 'actions' => 'lock']);
                $board_data['status'] = _gettext('Active');
                $board_data['lock_text'] = _gettext('Lock Board');
            }
            else
            {
                $board_data['lock_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                        http_build_query(
                                ['module' => 'admin', 'section' => 'manage-boards',
                                    'board-id' => $board_info['board_id'], 'actions' => 'unlock']);
                $board_data['status'] = _gettext('Locked');
                $board_data['lock_text'] = _gettext('Unlock Board');
            }

            $board_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'manage-boards', 'board-id' => $board_info['board_id'],
                                'actions' => 'remove']);
            $this->render_data['board_list'][] = $board_data;
        }

        $this->render_data['alphanumeric_only'] = $this->domain->setting('only_alphanumeric_board_ids');
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function removeWarning(array $parameters, bool $data_only)
    {
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Manage Boards');
        $parameters['section'] = $parameters['section'] ?? _gettext('Remove');
        $parameters['is_manage'] = true;
        $board_id = $_GET['board-id'];
        $messages[] = sprintf(_gettext('You are about to delete the board: %s'), $board_id);
        $messages[] = _gettext(
                'This will wipe out all posts, settings, files, everything. There is no undo or recovery.');
        $messages[] = _gettext('Are you sure?');
        $link['text'] = _gettext('NOPE. Do not delete the board.');
        $link['url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(['module' => 'admin', 'section' => 'manage-boards']);
        $link2['text'] = _gettext('Confirmed. Delete the board.');
        $link2['url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                        ['module' => 'admin', 'section' => 'manage-boards', 'actions' => 'remove',
                            'action-confirmed' => 'true', 'board-id' => $board_id]);
        $parameters['extra_url_break'] = true;
        $output_interstitial = new OutputInterstitial($this->domain, $this->write_mode);
        echo $output_interstitial->render($parameters, $data_only, $messages, [$link, $link2]);
    }
}