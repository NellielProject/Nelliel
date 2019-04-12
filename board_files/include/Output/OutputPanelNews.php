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

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array(), bool $data_only = false)
    {
        $render_data = array();
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_news_access'))
        {
            nel_derp(470, _gettext('You are not allowed to access the news panel.'));
        }

        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $render_data['head'] = $output_head->render(['dotdot' => $dotdot]);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('News')];
        $render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'extra_data' => $extra_data], true);
        $news_entries = $this->database->executeFetchAll('SELECT * FROM "' . NEWS_TABLE . '" ORDER BY "time" ASC',
                PDO::FETCH_ASSOC);
        $bgclass = 'row1';
        $render_data['form_action'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                ['module' => 'news', 'action' => 'add']);

        foreach ($news_entries as $news_entry)
        {
            $entry_info = array();
            $entry_info['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $entry_info['headline'] = $news_entry['headline'];
            $entry_info['time'] = date('Y/m/d (D) H:i:s', $news_entry['time']);
            $entry_info['remove_link'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'news', 'action' => 'remove', 'entry' => $news_entry['entry']]);
            $render_data['news_entry'][] = $entry_info;
        }

        $render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/news_panel', $render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output($render_data, 'page', true);
        echo $output;
        return $output;
    }
}