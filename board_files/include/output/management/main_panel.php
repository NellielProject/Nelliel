<?php

function nel_generate_main_panel()
{
    $dbh = nel_database();
    $authorize = nel_authorize();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header(array(), $render, null, array('header' => 'MANAGE_GENERAL', 'sub_header' => 'MANAGE_OPTIONS'));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/main_panel.html');
    $board_entry = $dom->getElementById('board-entry');
    $insert_before = $board_entry->parentNode->lastChild;
    $boards = $dbh->executeFetchAll('SELECT * FROM "' . BOARD_DATA_TABLE . '"', PDO::FETCH_ASSOC);

    if($boards !== false)
    {
        foreach($boards as $board)
        {
            $entry = $board_entry->cloneNode(true);
            $board_entry->parentNode->insertBefore($entry, $insert_before);
            $entry->removeAttribute('id');
            $entry_elements = $entry->getAssociativeNodeArray('data-parse-id', $entry);
            $entry_elements['board-link']->extSetAttribute('href', PHP_SELF . '?manage=board&module=main-panel&board_id=' . $board['board_id']);
            $entry_elements['board-link']->extSetAttribute('title', $board['board_id']);
            $entry_elements['board-link']->setContent('/' . $board['board_id'] . '/');
        }
    }

    $manage_options = $dom->getElementById('manage-options');
    $create_board = $dom->getElementById('module-create-board');
    $create_board_elements = $create_board->getAssociativeNodeArray('data-parse-id', $create_board);
    $create_board_elements['module-link']->extSetAttribute('href', PHP_SELF . '?manage=general&module=create-board');
    $staff = $dom->getElementById('module-staff');
    $staff_elements = $create_board->getAssociativeNodeArray('data-parse-id', $staff);
    $staff_elements['module-link']->extSetAttribute('href', PHP_SELF . '?manage=general&module=staff');
    $site_settings = $dom->getElementById('module-site-settings');
    $site_settings_elements = $create_board->getAssociativeNodeArray('data-parse-id', $site_settings);
    $site_settings_elements['module-link']->extSetAttribute('href', PHP_SELF . '?manage=general&module=site-settings');
    $board_entry->removeSelf();

    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    die();
}

function nel_generate_main_board_panel($board_id)
{
    $authorize = nel_authorize();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header(array(), $render, $board_id, array('header' => 'MANAGE_BOARD', 'sub_header' => 'MANAGE_OPTIONS'));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/main_board_panel.html');
    $dom->getElementById('cache-regen-form')->extSetAttribute('action', PHP_SELF . '?manage=board&module=regen&board_id=' . $board_id);
    $dom->getElementById('page-regen-form')->extSetAttribute('action', PHP_SELF . '?manage=board&module=regen&board_id=' . $board_id);
    $manage_options = $dom->getElementById('manage-options');
    $settings = $dom->getElementById('module-board-settings');

    if ($authorize->get_user_perm($_SESSION['username'], 'perm_config_access', $board_id))
    {
        $settings_elements = $manage_options->getAssociativeNodeArray('data-parse-id', $settings);
        $settings_elements['board-settings-link']->extSetAttribute('href', PHP_SELF . '?manage=board&module=board-settings&board_id=' . $board_id);
    }
    else
    {
        $settings->removeSelf();
    }

    $bans = $dom->getElementById('module-bans');

    if ($authorize->get_user_perm($_SESSION['username'], 'perm_ban_access', $board_id))
    {
        $bans_elements = $manage_options->getAssociativeNodeArray('data-parse-id', $bans);
        $bans_elements['bans-link']->extSetAttribute('href', PHP_SELF . '?manage=board&module=bans&board_id=' . $board_id);
    }
    else
    {
        $bans->removeSelf();
    }

    $threads = $dom->getElementById('module-threads');

    if ($authorize->get_user_perm($_SESSION['username'], 'perm_post_access', $board_id))
    {
        $threads_elements = $manage_options->getAssociativeNodeArray('data-parse-id', $threads);
        $threads_elements['threads-link']->extSetAttribute('href', PHP_SELF . '?manage=board&module=threads&board_id=' . $board_id);
    }
    else
    {
        $bans->removeSelf();
    }

    /*if ($authorize->get_user_perm($_SESSION['username'], 'perm_config_access', $board_id))
    {
        $dom->removeChild($dom->getElementById('select-settings-panel'));
    }

    if ($authorize->get_user_perm($_SESSION['username'], 'perm_ban_access', $board_id))
    {
        $dom->removeChild($dom->getElementById('select-ban-panel'));
    }

    if ($authorize->get_user_perm($_SESSION['username'], 'perm_post_access', $board_id))
    {
        $dom->removeChild($dom->getElementById('select-thread-panel'));
    }

    if ($authorize->get_user_perm($_SESSION['username'], 'perm_modmode_access', $board_id))
    {
        $dom->removeChild($dom->getElementById('select-mod-mode'));
    }*/

    if ($authorize->get_user_perm($_SESSION['username'], 'perm_regen_index', $board_id))
    {
        $dom->removeChild($dom->getElementById('page-regen-form'));
    }

    if ($authorize->get_user_perm($_SESSION['username'], 'perm_regen_caches', $board_id))
    {
        $dom->removeChild($dom->getElementById('cache-regen-form'));
    }

    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    die();
}