<?php

function nel_render_main_panel()
{
    $dbh = nel_database();
    $authorize = nel_authorize();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Options')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/main_panel.html');
    $board_entry = $dom->getElementById('board-entry');
    $insert_before = $board_entry->parentNode->lastChild;
    $boards = $dbh->executeFetchAll('SELECT * FROM "' . BOARD_DATA_TABLE . '"', PDO::FETCH_ASSOC);

    if ($boards !== false)
    {
        foreach ($boards as $board)
        {
            $entry = $board_entry->cloneNode(true);
            $board_entry->parentNode->insertBefore($entry, $insert_before);
            $entry->removeAttribute('id');
            $entry_elements = $entry->getAssociativeNodeArray('data-parse-id', $entry);
            $entry_elements['board-link']->extSetAttribute('href',
                    PHP_SELF . '?manage=board&module=main-panel&board_id=' . $board['board_id']);
            $entry_elements['board-link']->extSetAttribute('title', $board['board_id']);
            $entry_elements['board-link']->setContent('/' . $board['board_id'] . '/');
        }
    }

    $board_entry->removeSelf();
    $manage_options = $dom->getElementById('manage-options');
    $create_board = $dom->getElementById('module-create-board');
    $create_board_elements = $create_board->getAssociativeNodeArray('data-parse-id', $create_board);
    $create_board_elements['module-link']->extSetAttribute('href', PHP_SELF . '?manage=general&module=create-board');
    $staff = $dom->getElementById('module-staff');
    $staff_elements = $staff->getAssociativeNodeArray('data-parse-id', $staff);
    $staff_elements['module-link']->extSetAttribute('href', PHP_SELF . '?manage=general&module=staff');
    $site_settings = $dom->getElementById('module-site-settings');
    $site_settings_elements = $site_settings->getAssociativeNodeArray('data-parse-id', $site_settings);
    $site_settings_elements['module-link']->extSetAttribute('href', PHP_SELF . '?manage=general&module=site-settings');
    $file_filters = $dom->getElementById('module-file-filter');
    $file_filters_elements = $file_filters->getAssociativeNodeArray('data-parse-id', $file_filters);
    $file_filters_elements['module-link']->extSetAttribute('href', PHP_SELF . '?manage=general&module=file-filter');
    $default_board_settings = $dom->getElementById('module-default-board-settings');
    $default_board_settings_elements = $default_board_settings->getAssociativeNodeArray('data-parse-id',
            $default_board_settings);
    $default_board_settings_elements['module-link']->extSetAttribute('href',
            PHP_SELF . '?manage=general&module=default-board-settings');
    $dom->getElementById('extract-gettext-form')->extSetAttribute('action', PHP_SELF . '?manage=general&module=language');

    nel_language()->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}

function nel_render_main_board_panel($board_id)
{
    $authorize = nel_authorize();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, $board_id,
            array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Options')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/main_board_panel.html');
    $dom->getElementById('cache-regen-form')->extSetAttribute('action',
            PHP_SELF . '?manage=board&module=regen&board_id=' . $board_id);
    $dom->getElementById('page-regen-form')->extSetAttribute('action',
            PHP_SELF . '?manage=board&module=regen&board_id=' . $board_id);
    $manage_options = $dom->getElementById('manage-options');
    $settings = $dom->getElementById('module-board-settings');

    if ($authorize->getUserPerm($_SESSION['username'], 'perm_config_access', $board_id))
    {
        $settings_elements = $manage_options->getAssociativeNodeArray('data-parse-id', $settings);
        $settings_elements['board-settings-link']->extSetAttribute('href',
                PHP_SELF . '?manage=board&module=board-settings&board_id=' . $board_id);
    }
    else
    {
        $settings->removeSelf();
    }

    $bans = $dom->getElementById('module-bans');

    if ($authorize->getUserPerm($_SESSION['username'], 'perm_ban_access', $board_id))
    {
        $bans_elements = $manage_options->getAssociativeNodeArray('data-parse-id', $bans);
        $bans_elements['bans-link']->extSetAttribute('href',
                PHP_SELF . '?manage=board&module=bans&board_id=' . $board_id);
    }
    else
    {
        $bans->removeSelf();
    }

    $threads = $dom->getElementById('module-threads');

    if ($authorize->getUserPerm($_SESSION['username'], 'perm_post_access', $board_id))
    {
        $threads_elements = $manage_options->getAssociativeNodeArray('data-parse-id', $threads);
        $threads_elements['threads-link']->extSetAttribute('href',
                PHP_SELF . '?manage=board&module=threads&board_id=' . $board_id);
    }
    else
    {
        $bans->removeSelf();
    }

    $modmode = $dom->getElementById('module-modmode');

    if (true)
    {
        $modmode_elements = $manage_options->getAssociativeNodeArray('data-parse-id', $modmode);
        $modmode_elements['modmode-link']->extSetAttribute('href',
                PHP_SELF . '?manage=modmode&module=view-index&section=0&board_id=' . $board_id);
    }
    else
    {
        $bans->removeSelf();
    }

    if (!$authorize->getUserPerm($_SESSION['username'], 'perm_regen_index', $board_id))
    {
        $dom->getElementById('page-regen-form')->removeSelf();
    }

    if (!$authorize->getUserPerm($_SESSION['username'], 'perm_regen_caches', $board_id))
    {
        $dom->getElementById('cache-regen-form')->removeSelf();
    }

    nel_language()->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}