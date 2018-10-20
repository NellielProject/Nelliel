<?php

function nel_render_main_panel()
{
    $dbh = nel_database();
    $language = new \Nelliel\language\Language(nel_authorize());
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
    $user = $authorize->getUser($_SESSION['username']);

    if ($boards !== false)
    {
        foreach ($boards as $board)
        {
            $entry = $board_entry->cloneNode(true);
            $board_entry->parentNode->insertBefore($entry, $insert_before);
            $entry->removeAttribute('id');
            $entry_elements = $entry->getElementsByAttributeName('data-parse-id', true);
            $entry_elements['board-link']->extSetAttribute('href',
                    PHP_SELF . '?manage=board&module=main-panel&board_id=' . $board['board_id']);
            $entry_elements['board-link']->extSetAttribute('title', $board['board_id']);
            $entry_elements['board-link']->setContent('/' . $board['board_id'] . '/');
        }
    }

    $board_entry->remove();
    $manage_options = $dom->getElementById('manage-options');
    $manage_options_nodes = $manage_options->getElementsByAttributeName('data-parse-id', true);

    if ($user->boardPerm('', 'perm_create_board'))
    {
        $manage_options_nodes['module-link-create-board']->extSetAttribute('href',
                PHP_SELF . '?manage=general&module=create-board');
    }
    else
    {
        $manage_options_nodes['module-link-create-board']->remove();
    }

    if ($user->boardPerm('', 'perm_user_access') || $user->boardPerm('', 'perm_role_access'))
    {
        $manage_options_nodes['module-link-staff']->extSetAttribute('href', PHP_SELF . '?manage=general&module=staff');
    }
    else
    {
        $manage_options_nodes['module-link-staff']->remove();
    }

    if ($user->boardPerm('', 'perm_site_config_access'))
    {
        $manage_options_nodes['module-link-site-settings']->extSetAttribute('href',
                PHP_SELF . '?manage=general&module=site-settings');
    }
    else
    {
        $manage_options_nodes['module-link-site-settings']->remove();
    }

    $manage_options_nodes['module-link-file-filters']->extSetAttribute('href',
            PHP_SELF . '?manage=general&module=file-filter');

    if ($user->boardPerm('', 'perm_board_defaults_access'))
    {
        $manage_options_nodes['module-link-board-defaults']->extSetAttribute('href',
                PHP_SELF . '?manage=general&module=default-board-settings');
    }
    else
    {
        $manage_options_nodes['module-link-board-defaults']->remove();
    }

    if ($user->boardPerm('', 'perm_reports_access'))
    {
        $manage_options_nodes['module-link-reports']->extSetAttribute('href',
                PHP_SELF . '?manage=general&module=reports');
    }
    else
    {
        $manage_options_nodes['module-link-reports']->remove();
    }

    if ($user->boardPerm('', 'perm_extract_gettext'))
    {
        $dom->getElementById('extract-gettext-form')->extSetAttribute('action',
                PHP_SELF . '?manage=general&module=language&action=extract-gettext');
    }
    else
    {
        $dom->getElementById('extract-gettext-form')->remove();
    }

    $language->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}

function nel_render_main_board_panel($board_id)
{
    $language = new \Nelliel\language\Language(nel_authorize());
    $authorize = nel_authorize();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, $board_id,
            array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Options')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/main_board_panel.html');
    $manage_options = $dom->getElementById('manage-options');
    $settings = $dom->getElementById('module-board-settings');
    $user = $authorize->getUser($_SESSION['username']);

    if ($user->boardPerm($board_id, 'perm_board_config_access'))
    {
        $settings_elements = $settings->getElementsByAttributeName('data-parse-id', true);
        $settings_elements['board-settings-link']->extSetAttribute('href',
                PHP_SELF . '?manage=board&module=board-settings&board_id=' . $board_id);
    }
    else
    {
        $settings->remove();
    }

    $bans = $dom->getElementById('module-bans');

    if ($user->boardPerm($board_id, 'perm_ban_access'))
    {
        $bans_elements = $bans->getElementsByAttributeName('data-parse-id', true);
        $bans_elements['bans-link']->extSetAttribute('href',
                PHP_SELF . '?manage=board&module=bans&board_id=' . $board_id);
    }
    else
    {
        $bans->remove();
    }

    $threads = $dom->getElementById('module-threads');

    if ($user->boardPerm($board_id, 'perm_threads_access'))
    {
        $threads_elements = $threads->getElementsByAttributeName('data-parse-id', true);
        $threads_elements['threads-link']->extSetAttribute('href',
                PHP_SELF . '?manage=board&module=threads&board_id=' . $board_id);
    }
    else
    {
        $bans->remove();
    }

    $modmode = $dom->getElementById('module-modmode');

    if ($user->boardPerm($board_id, 'perm_modmode_access'))
    {
        $modmode_elements = $modmode->getElementsByAttributeName('data-parse-id', true);
        $modmode_elements['modmode-link']->extSetAttribute('href',
                PHP_SELF . '?manage=modmode&module=view-index&section=0&board_id=' . $board_id);
    }
    else
    {
        $bans->remove();
    }

    if ($user->boardPerm($board_id, 'perm_regen_index'))
    {
        $dom->getElementById('regen-all-pages')->extSetAttribute('href',
                PHP_SELF . '?manage=board&module=regen&action=all-pages&board_id=' . $board_id);
    }
    else
    {
        $dom->getElementById('regen-all-pages')->remove();
    }

    if (!$user->boardPerm($board_id, 'perm_regen_caches'))
    {
        $dom->getElementById('regen-all-caches')->extSetAttribute('href',
                PHP_SELF . '?manage=board&module=regen&action=all-caches&board_id=' . $board_id);
    }
    else
    {
        $dom->getElementById('regen-all-caches')->remove();
    }

    $language->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}