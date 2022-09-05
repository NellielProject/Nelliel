<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelPages extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/pages_main');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Static Pages');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_PAGES_TABLE . '" WHERE "domain_id" = ?');
        $pages = $this->database->executePreparedFetchAll($prepared, [$this->domain->id()], PDO::FETCH_ASSOC);
        $this->render_data['new_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
            http_build_query(
                ['module' => 'admin', 'section' => 'pages', 'actions' => 'new', 'board-id' => $this->domain->id()]);
        $bgclass = 'row1';

        foreach ($pages as $page) {
            $page_data = array();
            $page_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $page_data['uri'] = $page['uri'];
            $page_data['title'] = $page['title'];
            $page_data['edit_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'pages', 'actions' => 'edit', 'page-id' => $page['page_id']]);
            $page_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'pages', 'actions' => 'remove', 'page-id' => $page['page_id']]);
            $this->render_data['pages_list'][] = $page_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function new(array $parameters, bool $data_only)
    {
        $parameters['section'] = $parameters['section'] ?? _gettext('New');
        $parameters['editing'] = false;
        return $this->edit($parameters, $data_only);
    }

    public function edit(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/pages_edit');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Static Pages');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $editing = $parameters['editing'] ?? true;

        if ($editing) {
            $page_id = $parameters['page_id'] ?? 0;
            $form_action = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'pages', 'actions' => 'update', 'page-id' => $page_id,
                        'board-id' => $this->domain->id()]);
            $prepared = $this->database->prepare('SELECT * FROM "' . NEL_PAGES_TABLE . '" WHERE "page_id" = ?');
            $page_data = $this->database->executePreparedFetch($prepared, [$page_id], PDO::FETCH_ASSOC);

            if ($page_data !== false) {
                $this->render_data['uri'] = $page_data['uri'];
                $this->render_data['title'] = $page_data['title'];
                $this->render_data['text'] = $page_data['text'];
                $this->render_data['domain_id'] = $page_data['domain_id'];
            }
        } else {
            $this->render_data['new_page'] = true;
            $form_action = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'pages', 'actions' => 'add', 'board-id' => $this->domain->id()]);
        }

        $this->render_data['form_action'] = $form_action;
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}