<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelNews extends Output
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
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('News')];
        $this->render_data['header'] = $output_header->general(['manage_headers' => $manage_headers], true);
        $news_entries = $this->database->executeFetchAll('SELECT * FROM "' . NEL_NEWS_TABLE . '" ORDER BY "time" ASC',
                PDO::FETCH_ASSOC);
        $bgclass = 'row1';
        $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(['module' => 'admin', 'section' => 'news', 'actions' => 'add']);

        foreach ($news_entries as $news_entry)
        {
            $entry_info = array();
            $entry_info['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $entry_info['headline'] = $news_entry['headline'];
            $entry_info['time'] = date('Y/m/d (D) H:i:s', $news_entry['time']);
            $entry_info['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'news', 'actions' => 'remove',
                                'entry' => $news_entry['entry']]);
            $this->render_data['news_entry'][] = $entry_info;
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/news_panel', $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}