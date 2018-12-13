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
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Filetype Management')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/filetypes_panel.html');

    $icon_sets = $database->executeFetchAll(
            'SELECT * FROM "' . FRONT_END_TABLE . '" WHERE "resource_type" = \'filetype-icon-set\' ORDER BY "entry" DESC',
            PDO::FETCH_ASSOC);

    $form_action = $url_constructor->dynamic(PHP_SELF,
            ['manage' => 'general', 'module' => 'filetypes', 'action' => 'add', 'section' => 'icon-set']);
    $dom->getElementById('add-icon-set-form')->extSetAttribute('action', $form_action);

    $icon_set_list = $dom->getElementById('icon-set-list');
    $icon_set_list_nodes = $icon_set_list->getElementsByAttributeName('data-parse-id', true);
    $i = 0;
    $bgclass = 'row1';

    foreach ($icon_sets as $icon_set)
    {
        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $icon_set_row = $dom->copyNode($icon_set_list_nodes['icon-set-row'], $icon_set_list, 'append');
        $icon_set_row->modifyAttribute('class', ' ' . $bgclass, 'after');
        $icon_set_row_nodes = $icon_set_row->getElementsByAttributeName('data-parse-id', true);
        $icon_set_row_nodes['icon-set-id']->setContent($icon_set['id']);
        $icon_set_row_nodes['icon-set-name']->setContent($icon_set['display_name']);
        $icon_set_row_nodes['icon-set-directory']->setContent($icon_set['location']);
        $remove_link = $url_constructor->dynamic(PHP_SELF,
                ['manage' => 'general', 'module' => 'filetypes', 'action' => 'remove', 'section' => 'icon-set',
                    'icon-set-id' => $icon_set['id']]);
        $icon_set_row_nodes['icon-set-remove-link']->extSetAttribute('href', $remove_link);
        $i ++;
    }

    $icon_set_list_nodes['icon-set-row']->remove();
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}