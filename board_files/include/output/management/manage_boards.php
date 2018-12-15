<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_manage_boards_panel($domain, $user)
{
    if (!$user->boardPerm('', 'perm_manage_boards_access'))
    {
        nel_derp(370, _gettext('You are not allowed to access the board manager panel.'));
    }

    $database = nel_database();
    $translator = new \Nelliel\Language\Translator();
    $url_constructor = new \Nelliel\URLConstructor();
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain->renderInstance(), null, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Create new board')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/manage_boards_panel_main.html');
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

        if ($board_info['locked'] == 0)
        {
            $board_nodes['status']->setContent(_gettext('Active'));
            $board_nodes['link-board-lock']->extSetAttribute('href',
                    $url_constructor->dynamic($base_path,
                            ['module' => 'manage-boards', 'board_id' => $board_info['board_id'], 'action' => 'lock']));
            $board_nodes['link-board-lock']->setContent(_gettext('Lock Board'));
        }
        else
        {
            $board_nodes['status']->setContent(_gettext('Locked'));
            $board_nodes['link-board-lock']->extSetAttribute('href',
                    $url_constructor->dynamic($base_path,
                            ['module' => 'manage-boards', 'board_id' => $board_info['board_id'],
                                'action' => 'unlock']));
            $board_nodes['link-board-lock']->setContent(_gettext('Unlock Board'));
        }

        $board_nodes['link-board-remove']->extSetAttribute('href',
                $url_constructor->dynamic($base_path,
                        ['module' => 'manage-boards', 'board_id' => $board_info['board_id'], 'action' => 'remove']));
        $board_nodes['link-board-remove']->setContent(_gettext('!!DANGER!! Remove Board'));
        $board_info_table->appendChild($temp_board_info_row);
    }

    $board_info_row->remove();
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}

function nel_render_board_removal_interstitial($domain, $message, $continue_link)
{
    $domain->renderInstance(new NellielTemplates\RenderCore());
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain->renderInstance(), null, $domain->id(),
            array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Confirm Board Deletion')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, '/management/interstitials/board_removal.html');
    $dom->getElementById('message-text')->setContent($message);
    $dom->getElementById('continue-link')->setContent($continue_link['text']);
    $dom->getElementById('continue-link')->extSetAttribute('href', $continue_link['href']);
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
}