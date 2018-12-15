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
    nel_render_general_header($domain->renderInstance(), null, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Styles')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/styles_panel.html');

    $styles = $database->executeFetchAll('SELECT * FROM "' . STYLES_TABLE . '" ORDER BY "entry" DESC', PDO::FETCH_ASSOC);

    $form_action = $url_constructor->dynamic(PHP_SELF, ['module' => 'styles', 'action' => 'add']);
    $dom->getElementById('add-style-form')->extSetAttribute('action', $form_action);

    $style_list = $dom->getElementById('style-list');
    $style_list_nodes = $style_list->getElementsByAttributeName('data-parse-id', true);
    $bgclass = 'row1';

    foreach ($styles as $style)
    {
        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $style_row = $dom->copyNode($style_list_nodes['style-row'], $style_list, 'append');
        $style_row->modifyAttribute('class', ' ' . $bgclass, 'after');
        $style_row_nodes = $style_row->getElementsByAttributeName('data-parse-id', true);
        $style_row_nodes['style-id']->setContent($style['id']);
        $style_row_nodes['style-name']->setContent($style['name']);
        $style_row_nodes['style-file']->setContent($style['file']);

        if ($style['is_default'] == 1)
        {
            $style_row_nodes['style-default-link']->remove();
            $style_row_nodes['style-remove-link']->remove();
            $style_row_nodes['style-action-2']->setContent(_gettext('Default Style'));
        }
        else
        {
            $default_link = $url_constructor->dynamic(PHP_SELF,
                    ['module' => 'styles', 'action' => 'make-default', 'style-id' => $style['id']]);
            $style_row_nodes['style-default-link']->extSetAttribute('href', $default_link);
            $remove_link = $url_constructor->dynamic(PHP_SELF,
                    ['module' => 'styles', 'action' => 'remove', 'style-id' => $style['id']]);
            $style_row_nodes['style-remove-link']->extSetAttribute('href', $remove_link);
        }
    }

    $style_list_nodes['style-row']->remove();
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}