<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_news_panel($user, \Nelliel\Domain $domain)
{
    if (!$user->domainPermission($domain, 'perm_news_access'))
    {
        nel_derp(470, _gettext('You are not allowed to access the news panel.'));
    }

    $database = nel_database();
    $url_constructor = new \Nelliel\URLConstructor();
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    $output_header = new \Nelliel\Output\OutputHeader($domain);
    $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('News')];
    $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/news_panel.html');
    $news_entries = $database->executeFetchAll('SELECT * FROM "' . NEWS_TABLE . '" ORDER BY "time" ASC',
            PDO::FETCH_ASSOC);
    $news_entry_list = $dom->getElementById('news-entry-list');
    $news_entry_list_nodes = $news_entry_list->getElementsByAttributeName('data-parse-id', true);
    $bgclass = 'row1';
    $form_action = $url_constructor->dynamic(MAIN_SCRIPT, ['module' => 'news', 'action' => 'add']);
    $dom->getElementById('add-news-form')->extSetAttribute('action', $form_action);

    foreach ($news_entries as $news_entry)
    {
        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $news_entry_row = $dom->copyNode($news_entry_list_nodes['news-entry-row'], $news_entry_list, 'append');
        $news_entry_row->extSetAttribute('class', $bgclass);
        $news_entry_row_nodes = $news_entry_row->getElementsByAttributeName('data-parse-id', true);
        $news_entry_row_nodes['headline']->setContent($news_entry['headline']);
        $news_entry_row_nodes['time']->setContent(date('Y/m/d (D) H:i:s', $news_entry['time']));
        $remove_link = $url_constructor->dynamic(MAIN_SCRIPT,
                ['module' => 'news', 'action' => 'remove', 'entry' => $news_entry['entry']]);
        $news_entry_row_nodes['news-remove-link']->extSetAttribute('href', $remove_link);
    }

    $news_entry_list_nodes['news-entry-row']->remove();
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}