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
    private $database;

    function __construct(Domain $domain)
    {
        $this->database = $domain->database();
        $this->domain = $domain;
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_filetypes_access'))
        {
            nel_derp(430, _gettext('You are not allowed to access the Filetypes panel.'));
        }

        $final_output = '';

        // Temp
        $this->render_instance = $this->domain->renderInstance();
        $this->render_instance->startRenderTimer();

        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Filetypes')];
        $final_output .= $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $template_loader = new \Mustache_Loader_FilesystemLoader($this->domain->templatePath(), ['extension' => '.html']);
        $render_instance = new \Mustache_Engine(['loader' => $template_loader]);
        $template_loader->load('management/panels/filetypes_panel');
        $filetypes = $this->database->executeFetchAll(
                'SELECT * FROM "' . FILETYPES_TABLE . '" WHERE "extension" <> \'\' ORDER BY "entry" ASC', PDO::FETCH_ASSOC);
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

        $this->render_instance->appendHTML($render_instance->render('management/panels/filetypes_panel', $render_input));
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}