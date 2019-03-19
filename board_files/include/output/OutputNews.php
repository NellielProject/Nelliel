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
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $final_output = '';

        // Temp
        $this->domain->renderActive(true);
        $this->render_instance = $this->domain->renderInstance();
        $this->render_instance->startRenderTimer();

        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['use_site_titles' => true];
        $final_output .= $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $template_loader = new \Mustache_Loader_FilesystemLoader($this->domain->templatePath(), ['extension' => '.html']);
        $render_instance = new \Mustache_Engine(['loader' => $template_loader]);
        $template_loader->load('news');
        $render_input['news_entries'] = $this->newsList();
        $this->render_instance->appendHTML($render_instance->render('news', $render_input));
        nel_render_general_footer($this->domain);
        $this->file_handler->writeFile(BASE_PATH . 'news.html', $this->render_instance->outputRenderSet());
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