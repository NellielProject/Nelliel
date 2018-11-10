<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_manage_boards_panel($user)
{
    if (!$user->boardPerm('', 'perm_manage_boards_access'))
    {
        nel_derp(370, _gettext('You are not allowed to access the board manager panel.'));
    }

    $database = nel_database();
    $translator = new \Nelliel\Language\Translator();
    $render = new NellielTemplates\RenderCore();
    $url_constructor = new \Nelliel\URLConstructor();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Create new board')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/manage_boards_panel_main.html');
    $dom->getElementById('create-board-form')->extSetAttribute('action', PHP_SELF . '?module=manage-boards&action=add');
    $board_data = $database->executeFetchAll('SELECT * FROM "' . BOARD_DATA_TABLE . '" ORDER BY "board_id" DESC',
            PDO::FETCH_ASSOC);
    $bgclass = 'row1';
    $board_info_table = $dom->getElementById('board-info-table');
    $board_info_row = $dom->getElementById('board-info-row');
    $base_domain = $_SERVER['SERVER_NAME'] . pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);
    $base_path = '//' . $base_domain . '/' . PHP_SELF;

    foreach ($board_data as $board_info)
    {
        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $temp_board_info_row = $board_info_row->cloneNode(true);
        $temp_board_info_row->extSetAttribute('class', $bgclass);
        $board_nodes = $temp_board_info_row->getElementsByAttributeName('data-parse-id', true);
        $board_nodes['board-id']->setContent($board_info['board_id']);
        $board_nodes['board-directory']->setContent($board_info['board_directory']);
        $board_nodes['db-prefix']->setContent($board_info['db_prefix']);
        $board_nodes['link-board-remove']->extSetAttribute('href',
                $url_constructor->dynamic($base_path,
                        ['module' => 'manage-boards', 'board_id' => $board_info['board_id'], 'action' => 'remove']));
        $board_info_table->appendChild($temp_board_info_row);
    }

    $board_info_row->remove();
    $translator->translateDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}