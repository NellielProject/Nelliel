<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelMarkup extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/markup_main');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Markup');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $markups = $this->database->executeFetchAll('SELECT * FROM "' . NEL_MARKUP_TABLE . '"', PDO::FETCH_ASSOC);
        $this->render_data['new_url'] = nel_build_router_url([$this->domain->id(), 'markup', 'new']);
        $bgclass = 'row1';

        foreach ($markups as $markup) {
            $markup_data = array();
            $markup_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $markup_data['markup_id'] = $markup['markup_id'];
            $markup_data['label'] = $markup['label'];
            $markup_data['type'] = $markup['type'];
            $markup_data['match'] = $markup['match_regex'];
            $markup_data['replace'] = $markup['replacement'];
            $markup_data['enabled'] = $markup['enabled'];
            $markup_data['notes'] = $markup['notes'];
            $markup_data['edit_url'] = nel_build_router_url(
                [$this->domain->id(), 'markup', $markup_data['markup_id'], 'modify']);

            if ($markup_data['enabled'] == 1) {
                $markup_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->id(), 'markup', $markup_data['markup_id'], 'disable']);
                $markup_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($markup_data['enabled'] == 0) {
                $markup_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->id(), 'markup', $markup_data['markup_id'], 'enable']);
                $markup_data['enable_disable_text'] = _gettext('Enable');
            }

            $markup_data['delete_url'] = nel_build_router_url(
                [$this->domain->id(), 'markup', $markup_data['markup_id'], 'delete']);
            $this->render_data['markup_list'][] = $markup_data;
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
        $this->setBodyTemplate('panels/markup_edit');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Markup');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $editing = $parameters['editing'] ?? true;

        if ($editing) {
            $markup_id = $parameters['markup_id'] ?? 0;
            $form_action = nel_build_router_url([$this->domain->id(), 'markup', $markup_id, 'modify']);
            $prepared = $this->database->prepare('SELECT * FROM "' . NEL_MARKUP_TABLE . '" WHERE "markup_id" = ?');
            $markup_data = $this->database->executePreparedFetch($prepared, [$markup_id], PDO::FETCH_ASSOC);

            if ($markup_data !== false) {
                $this->render_data['markup_id'] = $markup_data['markup_id'];
                $this->render_data['label'] = $markup_data['label'];
                $this->render_data['type'] = $markup_data['type'];
                $this->render_data['match'] = $markup_data['match_regex'];
                $this->render_data['replace'] = $markup_data['replacement'];
                $this->render_data['enabled'] = $markup_data['enabled'] == 1 ? 'checked' : '';
                $this->render_data['notes'] = $markup_data['notes'];
            }
        } else {
            $form_action = nel_build_router_url([$this->domain->id(), 'markup', 'new']);
        }

        $menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['type_select'] = $menu->markupTypes($markup_data['type'] ?? '', $data_only);
        $this->render_data['form_action'] = $form_action;
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}