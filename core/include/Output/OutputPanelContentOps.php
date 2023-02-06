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
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Content Ops');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['new_url'] = nel_build_router_url([$this->domain->id(), 'content-ops', 'new']);
        $content_ops = $this->database->executeFetchAll('SELECT * FROM "' . NEL_CONTENT_OPS_TABLE . '"',
            PDO::FETCH_ASSOC);
        $bgclass = 'row1';

        foreach ($content_ops as $content_op) {
            $content_op_data = array();
            $content_op_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $content_op_data['op_id'] = $content_op['op_id'];
            $content_op_data['label'] = $content_op['label'];
            $content_op_data['url'] = $content_op['url'];
            $content_op_data['images_only'] = $content_op['images_only'];
            $content_op_data['enabled'] = $content_op['enabled'];
            $content_op_data['notes'] = $content_op['notes'];
            $content_op_data['edit_url'] = nel_build_router_url(
                [$this->domain->id(), 'content-ops', $content_op_data['op_id'], 'modify']);

            if ($content_op_data['enabled'] == 1) {
                $content_op_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->id(), 'content-ops', $content_op_data['op_id'], 'disable']);
                $content_op_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($content_op_data['enabled'] == 0) {
                $content_op_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->id(), 'content-ops', $content_op_data['op_id'], 'enable']);
                $content_op_data['enable_disable_text'] = _gettext('Enable');
            }

            $content_op_data['delete_url'] = nel_build_router_url(
                [$this->domain->id(), 'content-ops', $content_op_data['op_id'], 'delete']);
            $this->render_data['content_ops_list'][] = $content_op_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
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
            $op_id = $parameters['op_id'] ?? '';
            $form_action = nel_build_router_url([$this->domain->id(), 'content-ops', $op_id, 'modify']);
            $prepared = $this->database->prepare('SELECT * FROM "' . NEL_CONTENT_OPS_TABLE . '" WHERE "op_id" = ?');
            $content_op_data = $this->database->executePreparedFetch($prepared, [$op_id], PDO::FETCH_ASSOC);

            if ($content_op_data !== false) {
                $this->render_data['op_id'] = $content_op_data['op_id'];
                $this->render_data['label'] = $content_op_data['label'];
                $this->render_data['url'] = $content_op_data['url'];
                $this->render_data['images_only'] = $content_op_data['images_only'] == 1 ? 'checked' : '';
                $this->render_data['enabled'] = $content_op_data['enabled'] == 1 ? 'checked' : '';
                $this->render_data['notes'] = $content_op_data['notes'];
            }
        } else {
            $form_action = nel_build_router_url([$this->domain->id(), 'content-ops', 'new']);
        }

        $this->render_data['form_action'] = $form_action;
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}