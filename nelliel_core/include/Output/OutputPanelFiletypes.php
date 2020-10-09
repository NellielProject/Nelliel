<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;
use Nelliel\Auth\AuthUser;

class OutputPanelFiletypes extends OutputCore
{

    function __construct(Domain $domain, bool $write_mode)
    {
        $this->domain = $domain;
        $this->write_mode = $write_mode;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $user = $parameters['user'];
        $this->permCheck($user);

        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Filetypes')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
        $filetypes = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_FILETYPES_TABLE . '" WHERE "base_extension" <> \'\' ORDER BY "entry" ASC',
                PDO::FETCH_ASSOC);
        $this->render_data['form_action'] = $this->url_constructor->dynamic(NEL_MAIN_SCRIPT,
                ['module' => 'filetypes', 'action' => 'add']);
        $this->render_data['new_filetype_url'] = $this->url_constructor->dynamic(NEL_MAIN_SCRIPT,
                ['module' => 'filetypes', 'action' => 'new']);
        $bgclass = 'row1';

        foreach ($filetypes as $filetype)
        {
            $filetype_data = array();
            $filetype_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $filetype_data['base_extension'] = $filetype['base_extension'];
            $filetype_data['type'] = $filetype['type'];
            $filetype_data['format'] = $filetype['format'];
            $filetype_data['mime'] = $filetype['mime'];
            $sub_extensions = '';

            if (!empty($filetype['sub_extensions']))
            {
                foreach (json_decode($filetype['sub_extensions'], true) as $sub_extension)
                {
                    $sub_extensions .= $sub_extension . ' ';
                }
            }

            $filetype_data['sub_extensions'] = substr($sub_extensions, 0, -1);
            $filetype_data['mime'] = $filetype['mime'];
            $filetype_data['id_regex'] = $filetype['id_regex'];
            $filetype_data['label'] = $filetype['label'];
            $filetype_data['edit_url'] = $this->url_constructor->dynamic(NEL_MAIN_SCRIPT,
                    ['module' => 'filetypes', 'action' => 'edit', 'filetype-id' => $filetype['entry']]);

            if ($filetype['enabled'] == 1)
            {
                $filetype_data['enable_disable_url'] = $this->url_constructor->dynamic(NEL_MAIN_SCRIPT,
                        ['module' => 'filetypes', 'action' => 'disable', 'filetype-id' => $filetype['entry']]);
                $filetype_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($filetype['enabled'] == 0)
            {
                $filetype_data['enable_disable_url'] = $this->url_constructor->dynamic(NEL_MAIN_SCRIPT,
                        ['module' => 'filetypes', 'action' => 'enable', 'filetype-id' => $filetype['entry']]);
                $filetype_data['enable_disable_text'] = _gettext('Enable');
            }

            $filetype_data['remove_url'] = $this->url_constructor->dynamic(NEL_MAIN_SCRIPT,
                    ['module' => 'filetypes', 'action' => 'remove', 'filetype-id' => $filetype['entry']]);
            $this->render_data['filetype_list'][] = $filetype_data;
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/filetypes_panel_main',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }

    public function add(array $parameters, bool $data_only)
    {
        return $this->edit($parameters, $data_only);
    }

    public function edit(array $parameters, bool $data_only)
    {
        $user = $parameters['user'];
        $this->permCheck($user);

        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Filetypes')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/filetypes_panel_edit',
                $this->render_data);
        $editing = $parameters['editing'] ?? false;

        if ($editing)
        {
            $entry = $parameters['entry'] ?? 0;
            $form_action = $this->url_constructor->dynamic(NEL_MAIN_SCRIPT,
                    ['module' => 'filetypes', 'action' => 'update', 'filetype-id' => $entry]);
            $prepared = $this->database->prepare('SELECT * FROM "' . NEL_FILETYPES_TABLE . '" WHERE "entry" = ?');
            $filetype_data = $this->database->executePreparedFetch($prepared, [$entry], PDO::FETCH_ASSOC);

            if ($filetype_data !== false)
            {
                $this->render_data['entry'] = $filetype_data['entry'];
                $this->render_data['base_extension'] = $filetype_data['base_extension'];
                $this->render_data['type'] = $filetype_data['type'];
                $this->render_data['format'] = $filetype_data['format'];
                $this->render_data['mime'] = $filetype_data['mime'];
                $sub_extensions = '';

                if (!empty($filetype_data['sub_extensions']))
                {
                    foreach (json_decode($filetype_data['sub_extensions'], true) as $sub_extension)
                    {
                        $sub_extensions .= $sub_extension . ' ';
                    }
                }

                $this->render_data['sub_extensions'] = substr($sub_extensions, 0, -1);
                $this->render_data['id_regex'] = $filetype_data['id_regex'];
                $this->render_data['label'] = $filetype_data['label'];
                $this->render_data['enabled_checked'] = $filetype_data['enabled'] == 1 ? 'checked' : '';
            }
        }
        else
        {
            $form_action = $this->url_constructor->dynamic(NEL_MAIN_SCRIPT,
                    ['module' => 'filetypes', 'action' => 'update']);
        }

        $this->render_data['form_action'] = $form_action;
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/filetypes_panel_edit',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }

    private function permCheck(AuthUser $user)
    {
        if (!$user->checkPermission($this->domain, 'perm_manage_filetypes'))
        {
            nel_derp(430, _gettext('You are not allowed to manage filetypes.'));
        }
    }
}