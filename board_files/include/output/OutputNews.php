<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\FileHandler;
use PDO;
use Nelliel\OutputFilter;

class OutputNews extends OutputCore
{

    function __construct(Domain $domain, FileHandler $file_handler, OutputFilter $output_filter)
    {
        $this->domain = $domain;
        $this->file_handler = $file_handler;
        $this->output_filter = $output_filter;
    }

    public function render(array $parameters = array())
    {
        $this->prepare('news.html');
        $this->domain->renderActive(true);
        nel_render_general_header($this->domain, null, ['use_site_titles' => true]);
        $this->newsList();
        $this->domain->translator()->translateDom($this->dom, $this->domain->setting('language'));
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        $this->file_handler->writeFile(BASE_PATH . 'news.html', $this->render_instance->outputRenderSet());
    }

    private function newsList(int $limit = 0)
    {
        $database = nel_database();
        $authorization = new \Nelliel\Auth\Authorization(nel_database());
        $news_entries = $database->executeFetchAll('SELECT * FROM "' . NEWS_TABLE . '" ORDER BY "time" ASC',
                PDO::FETCH_ASSOC);
        $news_page = $this->dom->getElementById('news-page');
        $news_page_nodes = $news_page->getElementsByAttributeName('data-parse-id', true);
        $limit_counter = 0;

        foreach ($news_entries as $news_entry)
        {
            if($limit !== 0 && $limit_counter >= $limit)
            {
                break;
            }

            $news_entry_div = $this->dom->copyNode($news_page_nodes['news-entry'], $news_page, 'append');
            $news_entry_nodes = $news_entry_div->getElementsByAttributeName('data-parse-id', true);
            $poster_name = $authorization->getUser($news_entry['poster_id'])->auth_data['display_name'];
            $news_entry_nodes['headline']->setContent($news_entry['headline']);
            $news_entry_nodes['poster']->setContent(' by ' . $poster_name);
            $news_entry_nodes['time']->setContent(' - ' . date('Y/m/d (D) H:i:s', $news_entry['time']));

            foreach ($this->output_filter->newlinesToArray($news_entry['text']) as $line)
            {
                $news_entry_nodes['text']->appendChild($this->dom->createTextNode($line));
                $news_entry_nodes['text']->appendChild($this->dom->createElement('br'));
            }

            ++$limit_counter;
        }

        $news_page_nodes['news-entry']->remove();
    }
}