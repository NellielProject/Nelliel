<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_news()
{
    $database = nel_database();
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $domain = new \Nelliel\DomainSite(new \Nelliel\CacheHandler(), nel_database());
    $translator = new \Nelliel\Language\Translator();
    $file_handler = new \Nelliel\FileHandler();
    $domain->renderActive(true);
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain, null, ['use_site_titles' => true]);
    $dom = nel_render_news_list($domain);
    $translator->translateDom($dom, $domain->setting('language'));
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    $file_handler->writeFile(BASE_PATH . 'news.html', $domain->renderInstance()->outputRenderSet());
}

function nel_render_news_list($domain)
{
    $database = nel_database();
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $output_filter = new \Nelliel\OutputFilter();
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'news.html');
    $news_entries = $database->executeFetchAll('SELECT * FROM "' . NEWS_TABLE . '" ORDER BY "time" ASC',
            PDO::FETCH_ASSOC);
    $news_page = $dom->getElementById('news-page');
    $news_page_nodes = $news_page->getElementsByAttributeName('data-parse-id', true);

    foreach ($news_entries as $news_entry)
    {
        $news_entry_div = $dom->copyNode($news_page_nodes['news-entry'], $news_page, 'append');
        $news_entry_nodes = $news_entry_div->getElementsByAttributeName('data-parse-id', true);
        $poster_name = $authorization->getUser($news_entry['poster_id'])->auth_data['display_name'];
        $news_entry_nodes['headline']->setContent($news_entry['headline']);
        $news_entry_nodes['poster']->setContent(' by ' . $poster_name);
        $news_entry_nodes['time']->setContent(' - ' . date('Y/m/d (D) H:i:s', $news_entry['time']));

        foreach ($output_filter->newlinesToArray($news_entry['text']) as $line)
        {
            $news_entry_nodes['text']->appendChild($dom->createTextNode($line));
            $news_entry_nodes['text']->appendChild($dom->createElement('br'));
        }
    }

    $news_page_nodes['news-entry']->remove();
    return $dom;
}

