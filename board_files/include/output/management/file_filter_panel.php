<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_file_filter_panel($user, $board_id = '')
{
    if (!$user->boardPerm($board_id, 'perm_file_filters_access'))
    {
        nel_derp(341, _gettext('You are not allowed to add file filters.'));
    }

    $dbh = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($dbh);
    $url_constructor = new \Nelliel\URLConstructor();
    $translator = new \Nelliel\Language\Translator();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, null,
            array('header' => _gettext('Board Management'), 'sub_header' => _gettext('File Filters')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/file_filter_panel.html');

    if ($board_id !== '')
    {
        $prepared = $dbh->prepare(
                'SELECT * FROM "' . FILE_FILTER_TABLE . '" WHERE "board_id" = ? ORDER BY "entry" DESC');
        $filters = $dbh->executePreparedFetchAll($prepared, [$board_id], PDO::FETCH_ASSOC);
    }
    else
    {
        $filters = $dbh->executeFetchAll('SELECT * FROM "' . FILE_FILTER_TABLE . '" ORDER BY "entry" DESC', PDO::FETCH_ASSOC);
    }

    $form_action = $url_constructor->dynamic(PHP_SELF, ['manage' => 'general', 'module' => 'file-filter', 'action' => 'add']);
    $dom->getElementById('add-file-filter-form')->extSetAttribute('action', $form_action);

    $filter_list = $dom->getElementById('filter-list');
    $filter_list_nodes = $filter_list->getElementsByAttributeName('data-parse-id', true);
    $i = 0;
    $bgclass = 'row1';

    foreach ($filters as $filter)
    {
        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $filter_row = $dom->copyNode($filter_list_nodes['file-filter-row'], $filter_list, 'append');
        $filter_row->modifyAttribute('class', ' ' . $bgclass, 'after');
        $filter_row_nodes = $filter_row->getElementsByAttributeName('data-parse-id', true);
        $filter_row_nodes['filter-id']->setContent($filter['entry']);
        $filter_row_nodes['hash-type']->setContent($filter['hash_type']);
        $filter_row_nodes['file-hash']->setContent(bin2hex($filter['file_hash']));
        $filter_row_nodes['file-notes']->setContent($filter['file_notes']);
        $filter_row_nodes['board-id']->setContent($filter['board_id']);
        $remove_link = $url_constructor->dynamic(PHP_SELF, ['manage' => 'general', 'module' => 'file-filter', 'action' => 'remove', 'filter-id' => $filter['entry']]);
        $filter_row_nodes['filter-remove-link']->extSetAttribute('href', $remove_link);
        $i ++;
    }

    $filter_list_nodes['file-filter-row']->remove();
    $translator->translateDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}