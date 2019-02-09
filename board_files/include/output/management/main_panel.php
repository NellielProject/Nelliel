<?php

function nel_render_main_panel(\Nelliel\Domain $domain, $user)
{
    $database = nel_database();
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Options')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/main_panel.html');
    $board_entry = $dom->getElementById('board-entry');
    $insert_before = $board_entry->parentNode->lastChild;
    $boards = $database->executeFetchAll('SELECT * FROM "' . BOARD_DATA_TABLE . '"', PDO::FETCH_ASSOC);

    if ($boards !== false)
    {
        foreach ($boards as $board)
        {
            $entry = $board_entry->cloneNode(true);
            $board_entry->parentNode->insertBefore($entry, $insert_before);
            $entry->removeAttribute('id');
            $entry_elements = $entry->getElementsByAttributeName('data-parse-id', true);
            $entry_elements['board-link']->extSetAttribute('href',
                    MAIN_SCRIPT . '?module=main-panel&board_id=' . $board['board_id']);
            $entry_elements['board-link']->extSetAttribute('title', $board['board_id']);
            $entry_elements['board-link']->setContent('/' . $board['board_id'] . '/');
        }
    }

    $board_entry->remove();
    $manage_options = $dom->getElementById('manage-options');
    $manage_options_nodes = $manage_options->getElementsByAttributeName('data-parse-id', true);

    if ($user->boardPerm('', 'perm_manage_boards_access'))
    {
        $manage_options_nodes['module-link-manage-boards']->extSetAttribute('href', MAIN_SCRIPT . '?module=manage-boards');
    }
    else
    {
        $manage_options_nodes['module-link-manage-boards']->remove();
    }

    if ($user->boardPerm('', 'perm_user_access'))
    {
        $manage_options_nodes['module-link-users']->extSetAttribute('href', MAIN_SCRIPT . '?module=users');
    }
    else
    {
        $manage_options_nodes['module-link-users']->remove();
    }

    if ($user->boardPerm('', 'perm_role_access'))
    {
        $manage_options_nodes['module-link-roles']->extSetAttribute('href', MAIN_SCRIPT . '?module=roles');
    }
    else
    {
        $manage_options_nodes['module-link-roles']->remove();
    }

    if ($user->boardPerm('', 'perm_site_config_access'))
    {
        $manage_options_nodes['module-link-site-settings']->extSetAttribute('href', MAIN_SCRIPT . '?module=site-settings');
    }
    else
    {
        $manage_options_nodes['module-link-site-settings']->remove();
    }

    if ($user->boardPerm('', 'perm_file_filters_access'))
    {
        $manage_options_nodes['module-link-file-filters']->extSetAttribute('href', MAIN_SCRIPT . '?module=file-filters');
    }
    else
    {
        $manage_options_nodes['module-link-file-filters']->remove();
    }

    if ($user->boardPerm('', 'perm_board_defaults_access'))
    {
        $manage_options_nodes['module-link-board-defaults']->extSetAttribute('href',
                MAIN_SCRIPT . '?module=default-board-settings');
    }
    else
    {
        $manage_options_nodes['module-link-board-defaults']->remove();
    }

    if ($user->boardPerm('', 'perm_reports_access'))
    {
        $manage_options_nodes['module-link-reports']->extSetAttribute('href', MAIN_SCRIPT . '?module=reports');
    }
    else
    {
        $manage_options_nodes['module-link-reports']->remove();
    }

    if ($user->boardPerm('', 'perm_templates_access'))
    {
        $manage_options_nodes['module-link-templates']->extSetAttribute('href', MAIN_SCRIPT . '?module=templates');
    }
    else
    {
        $manage_options_nodes['module-link-templates']->remove();
    }

    if ($user->boardPerm('', 'perm_filetypes_access'))
    {
        $manage_options_nodes['module-link-filetypes']->extSetAttribute('href', MAIN_SCRIPT . '?module=filetypes');
    }
    else
    {
        $manage_options_nodes['module-link-filetypes']->remove();
    }

    if ($user->boardPerm('', 'perm_styles_access'))
    {
        $manage_options_nodes['module-link-styles']->extSetAttribute('href', MAIN_SCRIPT . '?module=styles');
    }
    else
    {
        $manage_options_nodes['module-link-styles']->remove();
    }

    if ($user->boardPerm('', 'perm_permissions_access'))
    {
        $manage_options_nodes['module-link-permissions']->extSetAttribute('href', MAIN_SCRIPT . '?module=permissions');
    }
    else
    {
        $manage_options_nodes['module-link-permissions']->remove();
    }

    if ($user->boardPerm('', 'perm_icon_sets_access'))
    {
        $manage_options_nodes['module-link-icon-sets']->extSetAttribute('href', MAIN_SCRIPT . '?module=icon-sets');
    }
    else
    {
        $manage_options_nodes['module-link-icon-sets']->remove();
    }

    if ($user->boardPerm('', 'perm_news_access'))
    {
        $manage_options_nodes['module-link-news']->extSetAttribute('href', MAIN_SCRIPT . '?module=news');
    }
    else
    {
        $manage_options_nodes['module-link-news']->remove();
    }

    if ($user->boardPerm('', 'perm_extract_gettext'))
    {
        $manage_options_nodes['module-extract-gettext']->extSetAttribute('href',
                MAIN_SCRIPT . '?module=language&action=extract-gettext');
    }
    else
    {
        $manage_options_nodes['module-extract-gettext']->remove();
    }

    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}

function nel_render_main_board_panel($domain)
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain, null,
            array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Options')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/main_board_panel.html');
    $manage_options = $dom->getElementById('manage-options');
    $settings = $dom->getElementById('module-board-settings');
    $session = new \Nelliel\Session($authorization, true);
    $user = $session->sessionUser();

    if ($user->boardPerm($domain->id(), 'perm_board_config_access'))
    {
        $settings_elements = $settings->getElementsByAttributeName('data-parse-id', true);
        $settings_elements['board-settings-link']->extSetAttribute('href',
                MAIN_SCRIPT . '?module=board-settings&board_id=' . $domain->id());
    }
    else
    {
        $settings->remove();
    }

    $bans = $dom->getElementById('module-bans');

    if ($user->boardPerm($domain->id(), 'perm_ban_access'))
    {
        $bans_elements = $bans->getElementsByAttributeName('data-parse-id', true);
        $bans_elements['bans-link']->extSetAttribute('href', MAIN_SCRIPT . '?module=bans&board_id=' . $domain->id());
    }
    else
    {
        $bans->remove();
    }

    $threads = $dom->getElementById('module-threads');

    if ($user->boardPerm($domain->id(), 'perm_threads_access'))
    {
        $threads_elements = $threads->getElementsByAttributeName('data-parse-id', true);
        $threads_elements['threads-link']->extSetAttribute('href',
                MAIN_SCRIPT . '?module=threads-admin&board_id=' . $domain->id());
    }
    else
    {
        $bans->remove();
    }

    $modmode = $dom->getElementById('module-modmode');

    if ($user->boardPerm($domain->id(), 'perm_modmode_access'))
    {
        $modmode_elements = $modmode->getElementsByAttributeName('data-parse-id', true);
        $modmode_elements['modmode-link']->extSetAttribute('href',
                MAIN_SCRIPT . '?module=render&action=view-index&index=0&board_id=' . $domain->id() . '&modmode=true');
    }
    else
    {
        $bans->remove();
    }

    $reports = $dom->getElementById('module-reports');

    if ($user->boardPerm($domain->id(), 'perm_reports_access'))
    {
        $reports_elements = $reports->getElementsByAttributeName('data-parse-id', true);
        $reports_elements['reports-link']->extSetAttribute('href',
                MAIN_SCRIPT . '?module=reports&board_id=' . $domain->id());
    }
    else
    {
        $reports->remove();
    }

    $file_filters = $dom->getElementById('module-file-filters');

    if ($user->boardPerm($domain->id(), 'perm_file_filters_access'))
    {
        $file_filters_elements = $file_filters->getElementsByAttributeName('data-parse-id', true);
        $file_filters_elements['file-filters-link']->extSetAttribute('href',
                MAIN_SCRIPT . '?module=file-filter&board_id=' . $domain->id());
    }
    else
    {
        $file_filters->remove();
    }

    if ($user->boardPerm($domain->id(), 'perm_regen_pages'))
    {
        $dom->getElementById('regen-all-pages')->extSetAttribute('href',
                MAIN_SCRIPT . '?module=regen&action=board-all-pages&board_id=' . $domain->id());
    }
    else
    {
        $dom->getElementById('regen-all-pages')->parentNode->remove();
    }

    if ($user->boardPerm($domain->id(), 'perm_regen_cache'))
    {
        $dom->getElementById('regen-all-caches')->extSetAttribute('href',
                MAIN_SCRIPT . '?module=regen&action=board-all-caches&board_id=' . $domain->id());
    }
    else
    {
        $dom->getElementById('regen-all-caches')->parentNode->remove();
    }

    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}