<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

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
        $this->setupTimer();
        $this->setBodyTemplate('panels/news');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('News');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $news_entries = $this->database->executeFetchAll('SELECT * FROM "' . NEL_NEWS_TABLE . '" ORDER BY "time" ASC',
            PDO::FETCH_ASSOC);
        $bgclass = 'row1';
        $this->render_data['form_action'] = nel_build_router_url([$this->domain->uri(), 'news', 'new']);

        foreach ($news_entries as $news_entry) {
            $entry_info = array();
            $entry_info['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $entry_info['name'] = $news_entry['name'];
            $entry_info['headline'] = $news_entry['headline'];
            $entry_info['time'] = $this->domain->domainDateTime(intval($news_entry['time']))->format(
                $this->site_domain->setting('control_panel_list_time_format'));
            $entry_info['delete_url'] = nel_build_router_url(
                [$this->domain->uri(), 'news', $news_entry['article_id'], 'delete']);
            $this->render_data['news_entry'][] = $entry_info;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}