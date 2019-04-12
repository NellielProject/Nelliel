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

    public function render(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_news_access'))
        {
            nel_derp(470, _gettext('You are not allowed to access the news panel.'));
        }

        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('News')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
        $news_entries = $this->database->executeFetchAll('SELECT * FROM "' . NEWS_TABLE . '" ORDER BY "time" ASC',
                PDO::FETCH_ASSOC);
        $bgclass = 'row1';
        $this->render_data['form_action'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
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
            $this->render_data['news_entry'][] = $entry_info;
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/news_panel',
                $this->render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('page', $data_only, true);
        echo $output;
        return $output;
    }
}