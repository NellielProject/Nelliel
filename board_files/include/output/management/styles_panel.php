<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_styles_panel($user, $domain)
{
    if (!$user->boardPerm($domain->id(), 'perm_styles_access'))
    {
        nel_derp(341, _gettext('You are not allowed to access the filetypes panel.'));
    }

    $database = nel_database();
    $url_constructor = new \Nelliel\URLConstructor();
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Styles')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/styles_panel.html');

    $ini_parser = new \Nelliel\INIParser(new \Nelliel\FileHandler());
    $style_inis = $ini_parser->parseDirectories(STYLES_WEB_PATH, 'style_info.ini');
    $styles = $database->executeFetchAll(
            'SELECT * FROM "' . ASSETS_TABLE . '" WHERE "type" = \'style\' ORDER BY "entry" ASC, "is_default" DESC', PDO::FETCH_ASSOC);
    $installed_ids = array();
    $installed_style_list = $dom->getElementById('installed-style-list');
    $installed_style_list_nodes = $installed_style_list->getElementsByAttributeName('data-parse-id', true);
    $bgclass = 'row1';

    foreach ($styles as $style)
    {
        $style_info = json_decode($style['info'], true);
        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $installed_ids[] = $style['id'];
        $style_row = $dom->copyNode($installed_style_list_nodes['style-row'], $installed_style_list,
                'append');
        $style_row->modifyAttribute('class', ' ' . $bgclass, 'after');
        $style_row_nodes = $style_row->getElementsByAttributeName('data-parse-id', true);
        $style_row_nodes['id']->setContent($style['id']);
        $style_row_nodes['style_type']->setContent(strtoupper($style_info['style_type']));
        $style_row_nodes['name']->setContent($style_info['name']);
        $style_row_nodes['directory']->setContent($style_info['directory']);

        if ($style['is_default'] == 1)
        {
            $style_row_nodes['default-link']->remove();
            $style_row_nodes['remove-link']->remove();
            $style_row_nodes['action-1']->setContent(_gettext('Default Style'));
        }
        else
        {
            $default_link = $url_constructor->dynamic(PHP_SELF,
                    ['module' => 'styles', 'action' => 'make-default', 'style-id' => $style['id'],
                    'style-type' => $style_info['style_type']]);
                    $style_row_nodes['default-link']->extSetAttribute('href', $default_link);
                    $remove_link = $url_constructor->dynamic(PHP_SELF,
                            ['module' => 'styles', 'action' => 'remove', 'style-id' => $style['id'],
                            'set-type' => $style_info['style_type']]);
                            $style_row_nodes['remove-link']->extSetAttribute('href', $remove_link);
        }
    }

    $installed_style_list_nodes['style-row']->remove();

    $available_style_list = $dom->getElementById('available-style-list');
    $available_style_list_nodes = $available_style_list->getElementsByAttributeName('data-parse-id', true);
    $bgclass = 'row1';

    foreach ($style_inis as $style)
    {
        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $style_row = $dom->copyNode($available_style_list_nodes['style-row'], $available_style_list,
                'append');
        $style_row->modifyAttribute('class', ' ' . $bgclass, 'after');
        $style_row_nodes = $style_row->getElementsByAttributeName('data-parse-id', true);
        $style_row_nodes['id']->setContent($style['id']);
        $style_row_nodes['style_type']->setContent(strtoupper($style['style_type']));
        $style_row_nodes['name']->setContent($style['name']);
        $style_row_nodes['directory']->setContent($style['directory']);

        if (in_array($style['id'], $installed_ids))
        {
            $style_row_nodes['install-link']->remove();
            $style_row_nodes['action-1']->setContent(_gettext('Style Installed'));
        }
        else
        {
            $install_link = $url_constructor->dynamic(PHP_SELF,
                    ['module' => 'styles', 'action' => 'add', 'style-id' => $style['id'],
                    'style-type' => $style['style_type']]);
                    $style_row_nodes['install-link']->extSetAttribute('href', $install_link);
        }
    }

    $available_style_list_nodes['style-row']->remove();
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}