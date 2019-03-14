<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelNews extends OutputCore
{
    private $database;

    function __construct(Domain $domain)
    {
        $this->database = $domain->database();
        $this->domain = $domain;
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_news_access'))
        {
            nel_derp(470, _gettext('You are not allowed to access the news panel.'));
        }

        $this->prepare('management/news_panel.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('News')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $news_entries = $this->database->executeFetchAll('SELECT * FROM "' . NEWS_TABLE . '" ORDER BY "time" ASC',
                PDO::FETCH_ASSOC);
        $news_entry_list = $this->dom->getElementById('news-entry-list');
        $news_entry_list_nodes = $news_entry_list->getElementsByAttributeName('data-parse-id', true);
        $bgclass = 'row1';
        $form_action = $this->url_constructor->dynamic(MAIN_SCRIPT, ['module' => 'news', 'action' => 'add']);
        $this->dom->getElementById('add-news-form')->extSetAttribute('action', $form_action);

        foreach ($news_entries as $news_entry)
        {
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $news_entry_row = $this->dom->copyNode($news_entry_list_nodes['news-entry-row'], $news_entry_list, 'append');
            $news_entry_row->extSetAttribute('class', $bgclass);
            $news_entry_row_nodes = $news_entry_row->getElementsByAttributeName('data-parse-id', true);
            $news_entry_row_nodes['headline']->setContent($news_entry['headline']);
            $news_entry_row_nodes['time']->setContent(date('Y/m/d (D) H:i:s', $news_entry['time']));
            $remove_link = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'news', 'action' => 'remove', 'entry' => $news_entry['entry']]);
            $news_entry_row_nodes['news-remove-link']->extSetAttribute('href', $remove_link);
        }

        $news_entry_list_nodes['news-entry-row']->remove();
        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}