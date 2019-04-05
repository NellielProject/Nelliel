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

    public function render(array $parameters = array())
    {
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_filetypes_access'))
        {
            nel_derp(430, _gettext('You are not allowed to access the Filetypes panel.'));
        }

        $this->render_core->startTimer();

        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Filetypes')];
        $this->render_core->appendToOutput(
                $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]));
        $filetypes = $this->database->executeFetchAll(
                'SELECT * FROM "' . FILETYPES_TABLE . '" WHERE "extension" <> \'\' ORDER BY "entry" ASC',
                PDO::FETCH_ASSOC);
        $form_action = $this->url_constructor->dynamic(MAIN_SCRIPT, ['module' => 'filetypes', 'action' => 'add']);
        $render_input['form_action'] = $form_action;
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
            $render_input['filetype_list'][] = $filetype_data;
        }

        $this->render_core->appendToOutput(
                $this->render_core->renderFromTemplateFile('management/panels/filetypes_panel', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        echo $this->render_core->getOutput();
        nel_clean_exit();
    }
}