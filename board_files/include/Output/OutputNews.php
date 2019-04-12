<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputNews extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $this->startTimer();
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['use_site_titles' => true];
        $this->render_core->appendToOutput($output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]));
        $render_data['news_entries'] = $this->newsList();
        $this->render_core->appendToOutput($this->render_core->renderFromTemplateFile('news', $render_data));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        $this->file_handler->writeFile(BASE_PATH . 'news.html', $this->render_core->getOutput());
    }

    private function newsList(int $limit = 0)
    {
        $database = $this->domain->database();
        $authorization = new \Nelliel\Auth\Authorization($database);
        $news_entries = $database->executeFetchAll('SELECT * FROM "' . NEWS_TABLE . '" ORDER BY "time" ASC',
                PDO::FETCH_ASSOC);
        $limit_counter = 0;
        $entry_list = array();

        foreach ($news_entries as $news_entry)
        {
            if($limit !== 0 && $limit_counter >= $limit)
            {
                break;
            }

            $news_info = array();
            $news_info['headline'] = $news_entry['headline'];
            $poster_name = $authorization->getUser($news_entry['poster_id'])->auth_data['display_name'];
            $news_info['poster'] = ' by ' . $poster_name;
            $news_info['time'] = ' - ' . date('Y/m/d (D) H:i:s', $news_entry['time']);
            $news_info['news_lines'] = array();

            foreach ($this->output_filter->newlinesToArray($news_entry['text']) as $line)
            {
                $news_info['news_lines'][]['news_line'] = $line;
            }

            $entry_list[] = $news_info;
            ++$limit_counter;
        }

        return $entry_list;
    }
}