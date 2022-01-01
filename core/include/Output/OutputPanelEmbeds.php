<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelEmbeds extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/embeds_main');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Embeds');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $embeds = $this->database->executeFetchAll('SELECT * FROM "' . NEL_EMBEDS_TABLE . '"',
            PDO::FETCH_ASSOC);
        $bgclass = 'row1';
        $this->render_data['new_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
            http_build_query(['module' => 'admin', 'section' => 'embeds', 'actions' => 'new']);

        foreach ($embeds as $embed) {
            $embed_data = array();
            $embed_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $embed_data['embed_id'] = $embed['embed_id'];
            $embed_data['label'] = $embed['label'];
            $embed_data['url'] = $embed['url'];
            $embed_data['enabled'] = $embed['enabled'];
            $embed_data['notes'] = $embed['notes'];
            $embed_data['edit_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'embeds', 'actions' => 'edit',
                        'embed-id' => $embed_data['embed_id']]);

            if ($embed_data['enabled'] == 1) {
                $embed_data['enable_disable_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                        ['module' => 'admin', 'section' => 'embeds', 'actions' => 'disable',
                            'embed-id' => $embed_data['embed_id']]);
                $embed_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($embed_data['enabled'] == 0) {
                $embed_data['enable_disable_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                        ['module' => 'admin', 'section' => 'embeds', 'actions' => 'enable',
                            'embed-id' => $embed_data['embed_id']]);
                $embed_data['enable_disable_text'] = _gettext('Enable');
            }

            $embed_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'embeds', 'actions' => 'remove',
                        'embed-id' => $embed_data['embed_id']]);
            $this->render_data['embeds_list'][] = $embed_data;
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
        $this->setBodyTemplate('panels/embeds_edit');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Embeds');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $editing = $parameters['editing'] ?? true;

        if ($editing) {
            $embed_id = $parameters['embed_id'] ?? 0;
            $form_action = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'embeds', 'actions' => 'update', 'embed-id' => $embed_id]);
            $prepared = $this->database->prepare('SELECT * FROM "' . NEL_EMBEDS_TABLE . '" WHERE "embed_id" = ?');
            $embed_data = $this->database->executePreparedFetch($prepared, [$embed_id], PDO::FETCH_ASSOC);

            if ($embed_data !== false) {
                $this->render_data['embed_id'] = $embed_data['embed_id'];
                $this->render_data['label'] = $embed_data['label'];
                $this->render_data['regex'] = $embed_data['regex'];
                $this->render_data['url'] = $embed_data['url'];
                $this->render_data['enabled'] = $embed_data['enabled'] == 1 ? 'checked' : '';
                $this->render_data['notes'] = $embed_data['notes'];
            }
        } else {
            $this->render_data['new_embed'] = true;
            $form_action = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(['module' => 'admin', 'section' => 'embeds', 'actions' => 'add']);
        }

        $this->render_data['form_action'] = $form_action;
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}