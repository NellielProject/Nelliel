<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use PDO;

class OutputNews extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $this->render_data['news_entries'] = $this->newsList();
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('news', $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        $this->file_handler->writeFile(NEL_BASE_PATH . 'news.html', $output);
    }

    private function newsList(int $limit = 0)
    {
        $database = $this->domain->database();
        $authorization = new \Nelliel\Auth\Authorization($database);
        $news_entries = $database->executeFetchAll('SELECT * FROM "' . NEL_NEWS_TABLE . '" ORDER BY "time" ASC',
                PDO::FETCH_ASSOC);
        $limit_counter = 0;
        $entry_list = array();

        foreach ($news_entries as $news_entry)
        {
            if ($limit !== 0 && $limit_counter >= $limit)
            {
                break;
            }

            $news_info = array();
            $news_info['headline'] = $news_entry['headline'];
            $poster_name = $authorization->getUser($news_entry['poster_id'])->auth_data['display_name'];
            $news_info['poster'] = ' by ' . $poster_name;
            $news_info['time'] = ' - ' . date('Y/m/d l H:i', $news_entry['time']);
            $news_info['news_lines'] = array();

            foreach ($this->output_filter->newlinesToArray($news_entry['text']) as $line)
            {
                $news_info['news_lines'][]['news_line'] = $line;
            }

            $entry_list[] = $news_info;
            ++ $limit_counter;
        }

        return $entry_list;
    }
}