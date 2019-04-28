<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelFiletypes extends OutputCore
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
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_filetypes_access'))
        {
            nel_derp(430, _gettext('You are not allowed to access the Filetypes panel.'));
        }

        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new OutputHeader($this->domain);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Filetypes')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
        $filetypes = $this->database->executeFetchAll(
                'SELECT * FROM "' . FILETYPES_TABLE . '" WHERE "extension" <> \'\' ORDER BY "entry" ASC',
                PDO::FETCH_ASSOC);
        $form_action = $this->url_constructor->dynamic(MAIN_SCRIPT, ['module' => 'filetypes', 'action' => 'add']);
        $this->render_data['form_action'] = $form_action;
        $bgclass = 'row1';

        foreach ($filetypes as $filetype)
        {
            $filetype_data = array();
            $filetype_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $filetype_data['extension'] = $filetype['extension'];
            $filetype_data['parent_extension'] = $filetype['parent_extension'];
            $filetype_data['type'] = $filetype['type'];
            $filetype_data['format'] = $filetype['format'];
            $filetype_data['mime'] = $filetype['mime'];
            $filetype_data['id_regex'] = $filetype['id_regex'];
            $filetype_data['label'] = $filetype['label'];
            $filetype_data['remove_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'filetypes', 'action' => 'remove', 'filetype-id' => $filetype['entry']]);
            $this->render_data['filetype_list'][] = $filetype_data;
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/filetypes_panel',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}