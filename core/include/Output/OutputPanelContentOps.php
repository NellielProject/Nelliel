<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelContentOps extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/content_ops_main');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Content Ops');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $content_ops = $this->database->executeFetchAll(
            'SELECT * FROM "' . NEL_CONTENT_OPS_TABLE . '" ORDER BY "entry" DESC', PDO::FETCH_ASSOC);
        $bgclass = 'row1';

        foreach ($content_ops as $content_op) {
            $content_op_data = array();
            $content_op_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $content_op_data['entry'] = $content_op['entry'];
            $content_op_data['content_op_label'] = $content_op['content_op_label'];
            $content_op_data['content_op_url'] = $content_op['content_op_url'];
            $content_op_data['images_only'] = $content_op['images_only'];
            $content_op_data['enabled'] = $content_op['enabled'];
            $content_op_data['notes'] = $content_op['notes'];
            $content_op_data['edit_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'content-ops', 'actions' => 'edit',
                        'content-op-id' => $content_op_data['entry']]);

            if ($content_op_data['enabled'] == 1) {
                $content_op_data['enable_disable_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                        ['module' => 'admin', 'section' => 'content-ops', 'actions' => 'disable',
                            'content-op-id' => $content_op_data['entry']]);
                $content_op_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($content_op_data['enabled'] == 0) {
                $content_op_data['enable_disable_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                        ['module' => 'admin', 'section' => 'content-ops', 'actions' => 'enable',
                            'content-op-id' => $content_op_data['entry']]);
                $content_op_data['enable_disable_text'] = _gettext('Enable');
            }

            $content_op_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'content-ops', 'actions' => 'remove',
                        'content-op-id' => $content_op_data['entry']]);
            $this->render_data['content_ops_list'][] = $content_op_data;
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
        $this->setBodyTemplate('panels/content_ops_edit');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Content Ops');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $editing = $parameters['editing'] ?? true;

        if ($editing) {
            $entry = $parameters['entry'] ?? 0;
            $form_action = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'content-ops', 'actions' => 'update', 'content-op-id' => $entry]);
            $prepared = $this->database->prepare('SELECT * FROM "' . NEL_CONTENT_OPS_TABLE . '" WHERE "entry" = ?');
            $content_op_data = $this->database->executePreparedFetch($prepared, [$entry], PDO::FETCH_ASSOC);

            if ($content_op_data !== false) {
                $this->render_data['entry'] = $content_op_data['entry'];
                $this->render_data['content_op_label'] = $content_op_data['content_op_label'];
                $this->render_data['content_op_url'] = $content_op_data['content_op_url'];
                $this->render_data['images_only'] = $content_op_data['images_only'] == 1 ? 'checked' : '';
                $this->render_data['enabled'] = $content_op_data['enabled'] == 1 ? 'checked' : '';
                $this->render_data['notes'] = $content_op_data['notes'];
            }
        } else {
            $form_action = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(['module' => 'admin', 'section' => 'content-ops', 'actions' => 'add']);
        }

        $this->render_data['form_action'] = $form_action;
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}