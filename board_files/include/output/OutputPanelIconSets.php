<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelIconSets extends OutputCore
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

        if (!$user->domainPermission($this->domain, 'perm_icon_sets_access'))
        {
            nel_derp(460, _gettext('You are not allowed to access the Icon Sets panel.'));
        }

        // Temp
        $this->render_instance = $this->domain->renderInstance();
        $this->render_instance->startRenderTimer();

        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Icon Sets')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $template_loader = new \Mustache_Loader_FilesystemLoader($this->domain->templatePath(), ['extension' => '.html']);
        $render_instance = new \Mustache_Engine(['loader' => $template_loader]);
        $template_loader->load('management/panels/icon_sets_panel');
        $icon_sets = $this->database->executeFetchAll(
                'SELECT * FROM "' . ASSETS_TABLE . '" WHERE "type" = \'icon-set\' ORDER BY "entry" ASC, "is_default" DESC', PDO::FETCH_ASSOC);
        $installed_ids = array();
        $bgclass = 'row1';

        foreach ($icon_sets as $icon_set)
        {
            $set_data = array();
            $icon_set_info = json_decode($icon_set['info'], true);
            $set_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $installed_ids[] = $icon_set['id'];
            $set_data['id'] = $icon_set['id'];
            $set_data['set_type'] = strtoupper($icon_set_info['set_type']);
            $set_data['name'] = $icon_set_info['name'];
            $set_data['directory'] = $icon_set_info['directory'];
            $set_data['is_default'] = $icon_set['is_default'] == 1;
            $set_data['default_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'icon-sets', 'action' => 'make-default', 'icon-set-id' => $icon_set['id'],
                        'set-type' => $icon_set_info['set_type']]);
            $set_data['remove_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'icon-sets', 'action' => 'remove', 'icon-set-id' => $icon_set['id'],
                        'set-type' => $icon_set_info['set_type']]);

            $render_input['installed_list'][] = $set_data;
        }

        $ini_parser = new \Nelliel\INIParser($this->file_handler);
        $icon_set_inis = $ini_parser->parseDirectories(ICON_SETS_WEB_PATH, 'icon_set_info.ini');
        $bgclass = 'row1';

        foreach ($icon_set_inis as $icon_set)
        {
            $set_data = array();
            $set_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $set_data['id'] = $icon_set['id'];
            $set_data['set_type'] = strtoupper($icon_set_info['set_type']);
            $set_data['name'] = $icon_set_info['name'];
            $set_data['directory'] = $icon_set_info['directory'];
            $set_data['is_installed'] = in_array($icon_set['id'], $installed_ids);
            $set_data['install_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'icon-sets', 'action' => 'add', 'icon-set-id' => $icon_set['id'],
                        'set-type' => $icon_set['set_type']]);
        }

        $this->render_instance->appendHTML($render_instance->render('management/panels/icon_sets_panel', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $output_footer->render(['dotdot' => '', 'styles' => false]);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}