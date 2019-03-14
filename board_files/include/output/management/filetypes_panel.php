<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_filetypes_panel($user, \Nelliel\Domain $domain)
{
    if (!$user->domainPermission($domain, 'perm_filetypes_access'))
    {
        nel_derp(430, _gettext('You are not allowed to access the Filetypes panel.'));
    }

    $database = nel_database();
    $url_constructor = new \Nelliel\URLConstructor();
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    $output_header = new \Nelliel\Output\OutputHeader($domain);
    $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Filetypes')];
    $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/filetypes_panel.html');
    $filetypes = $database->executeFetchAll(
            'SELECT * FROM "' . FILETYPES_TABLE . '" WHERE "extension" <> \'\' ORDER BY "entry" ASC', PDO::FETCH_ASSOC);
    $form_action = $url_constructor->dynamic(MAIN_SCRIPT, ['module' => 'filetypes', 'action' => 'add']);
    $dom->getElementById('add-filetype-form')->extSetAttribute('action', $form_action);

    $filetype_list = $dom->getElementById('filetype-list');
    $filetype_list_nodes = $filetype_list->getElementsByAttributeName('data-parse-id', true);
    $bgclass = 'row1';

    foreach ($filetypes as $filetype)
    {
        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $filetype_row = $dom->copyNode($filetype_list_nodes['filetype-row'], $filetype_list, 'append');
        $filetype_row_nodes = $filetype_row->getElementsByAttributeName('data-parse-id', true);
        $filetype_row->extSetAttribute('class', $bgclass);
        $filetype_row_nodes['extension']->setContent($filetype['extension']);
        $filetype_row_nodes['parent-extension']->setContent($filetype['parent_extension']);
        $filetype_row_nodes['type']->setContent($filetype['type']);
        $filetype_row_nodes['format']->setContent($filetype['format']);
        $filetype_row_nodes['mime']->setContent($filetype['mime']);
        $filetype_row_nodes['regex']->setContent($filetype['id_regex']);
        $filetype_row_nodes['label']->setContent($filetype['label']);
        $remove_link = $url_constructor->dynamic(MAIN_SCRIPT,
                ['module' => 'filetypes', 'action' => 'remove', 'filetype-id' => $filetype['entry']]);
        $filetype_row_nodes['filetype-remove-link']->extSetAttribute('href', $remove_link);
    }

    $filetype_list_nodes['filetype-row']->remove();
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}