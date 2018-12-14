<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_filetypes_panel($user, $domain)
{
    if (!$user->boardPerm($domain->id(), 'perm_filetypes_access'))
    {
        nel_derp(341, _gettext('You are not allowed to access the filetypes panel.'));
    }

    $database = nel_database();
    $url_constructor = new \Nelliel\URLConstructor();
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain->renderInstance(), null, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Filetypes')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/filetypes_panel.html');
    $ini_parser = new \Nelliel\INIParser(new \Nelliel\FileHandler());
    $icon_set_inis = $ini_parser->parseDirectories(FILETYPE_ICON_PATH, 'icon_set_info.ini');
    $icon_sets = $database->executeFetchAll(
            'SELECT * FROM "' . ICON_SET_TABLE . '" WHERE "set_type" = \'filetype\' ORDER BY "entry" ASC',
            PDO::FETCH_ASSOC);
    $installed_ids = array();
    $default_icon_set_id = '';
    $installed_icon_set_list = $dom->getElementById('installed-icon-set-list');
    $installed_icon_set_list_nodes = $installed_icon_set_list->getElementsByAttributeName('data-parse-id', true);
    $bgclass = 'row1';

    foreach ($icon_sets as $icon_set)
    {
        if ($icon_set['is_default'] == 1)
        {
            $default_icon_set_id = $icon_set['id'];
        }

        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $installed_ids[] = $icon_set['id'];
        $icon_set_row = $dom->copyNode($installed_icon_set_list_nodes['icon-set-row'], $installed_icon_set_list,
                'append');
        $icon_set_row->modifyAttribute('class', ' ' . $bgclass, 'after');
        $icon_set_row_nodes = $icon_set_row->getElementsByAttributeName('data-parse-id', true);
        $icon_set_row_nodes['icon-set-id']->setContent($icon_set['id']);
        $icon_set_row_nodes['icon-set-name']->setContent($icon_set['name']);
        $icon_set_row_nodes['icon-set-directory']->setContent($icon_set['directory']);

        if ($icon_set['is_default'] == 1)
        {
            $icon_set_row_nodes['icon-set-default-link']->remove();
            $icon_set_row_nodes['icon-set-remove-link']->remove();
            $icon_set_row_nodes['icon-set-action-1']->setContent(_gettext('Default Icon Set'));
        }
        else
        {
            $default_link = $url_constructor->dynamic(PHP_SELF,
                    ['manage' => 'general', 'module' => 'filetypes', 'action' => 'make-default', 'section' => 'icon-set',
                    'icon-set-id' => $icon_set['id']]);
                    $icon_set_row_nodes['icon-set-default-link']->extSetAttribute('href', $default_link);
            $remove_link = $url_constructor->dynamic(PHP_SELF,
                    ['manage' => 'general', 'module' => 'filetypes', 'action' => 'remove', 'section' => 'icon-set',
                    'icon-set-id' => $icon_set['id']]);
                    $icon_set_row_nodes['icon-set-remove-link']->extSetAttribute('href', $remove_link);
        }
    }

    $installed_icon_set_list_nodes['icon-set-row']->remove();

    $available_icon_set_list = $dom->getElementById('available-icon-set-list');
    $available_icon_set_list_nodes = $available_icon_set_list->getElementsByAttributeName('data-parse-id', true);
    $bgclass = 'row1';

    foreach ($icon_set_inis as $icon_set)
    {
        if ($icon_set['id'] === $default_icon_set_id)
        {
            continue;
        }

        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $icon_set_row = $dom->copyNode($available_icon_set_list_nodes['icon-set-row'], $available_icon_set_list,
                'append');
        $icon_set_row->modifyAttribute('class', ' ' . $bgclass, 'after');
        $icon_set_row_nodes = $icon_set_row->getElementsByAttributeName('data-parse-id', true);
        $icon_set_row_nodes['icon-set-id']->setContent($icon_set['id']);
        $icon_set_row_nodes['icon-set-name']->setContent($icon_set['name']);
        $icon_set_row_nodes['icon-set-directory']->setContent($icon_set['directory']);

        if (in_array($icon_set['id'], $installed_ids))
        {
            $icon_set_row_nodes['icon-set-install-link']->remove();
            $icon_set_row_nodes['icon-set-action-1']->setContent(_gettext('Icon Set Installed'));
        }
        else
        {
            $install_link = $url_constructor->dynamic(PHP_SELF,
                    ['manage' => 'general', 'module' => 'filetypes', 'action' => 'add', 'section' => 'icon-set',
                    'icon-set-id' => $icon_set['id']]);
                    $icon_set_row_nodes['icon-set-install-link']->extSetAttribute('href', $install_link);
        }
    }

    $available_icon_set_list_nodes['icon-set-row']->remove();
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}