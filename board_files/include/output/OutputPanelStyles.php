<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelStyles extends OutputCore
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

        if (!$user->domainPermission($this->domain, 'perm_styles_access'))
        {
            nel_derp(440, _gettext('You are not allowed to access the styles panel.'));
        }

        $this->prepare('management/styles_panel.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Styles')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);

        $ini_parser = new \Nelliel\INIParser(new \Nelliel\FileHandler());
        $style_inis = $ini_parser->parseDirectories(STYLES_WEB_PATH, 'style_info.ini');
        $styles = $this->database->executeFetchAll(
                'SELECT * FROM "' . ASSETS_TABLE . '" WHERE "type" = \'style\' ORDER BY "entry" ASC, "is_default" DESC', PDO::FETCH_ASSOC);
        $installed_ids = array();
        $installed_style_list = $this->dom->getElementById('installed-style-list');
        $installed_style_list_nodes = $installed_style_list->getElementsByAttributeName('data-parse-id', true);
        $bgclass = 'row1';

        foreach ($styles as $style)
        {
            $style_info = json_decode($style['info'], true);
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $installed_ids[] = $style['id'];
            $style_row = $this->dom->copyNode($installed_style_list_nodes['style-row'], $installed_style_list,
                    'append');
            $style_row->extSetAttribute('class', $bgclass);
            $style_row_nodes = $style_row->getElementsByAttributeName('data-parse-id', true);
            $style_row_nodes['id']->setContent($style['id']);
            $style_row_nodes['style_type']->setContent(strtoupper($style_info['style_type']));
            $style_row_nodes['name']->setContent($style_info['name']);
            $style_row_nodes['directory']->setContent($style_info['directory']);

            if ($style['is_default'] == 1)
            {
                $style_row_nodes['default-link']->remove();
                $style_row_nodes['remove-link']->remove();
                $style_row_nodes['action-1']->setContent(_gettext('Default Style'));
            }
            else
            {
                $default_link = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'styles', 'action' => 'make-default', 'style-id' => $style['id'],
                        'style-type' => $style_info['style_type']]);
                        $style_row_nodes['default-link']->extSetAttribute('href', $default_link);
                        $remove_link = $this->url_constructor->dynamic(MAIN_SCRIPT,
                                ['module' => 'styles', 'action' => 'remove', 'style-id' => $style['id'],
                                'set-type' => $style_info['style_type']]);
                                $style_row_nodes['remove-link']->extSetAttribute('href', $remove_link);
            }
        }

        $installed_style_list_nodes['style-row']->remove();

        $available_style_list = $this->dom->getElementById('available-style-list');
        $available_style_list_nodes = $available_style_list->getElementsByAttributeName('data-parse-id', true);
        $bgclass = 'row1';

        foreach ($style_inis as $style)
        {
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $style_row = $this->dom->copyNode($available_style_list_nodes['style-row'], $available_style_list,
                    'append');
            $style_row->extSetAttribute('class', $bgclass);
            $style_row_nodes = $style_row->getElementsByAttributeName('data-parse-id', true);
            $style_row_nodes['id']->setContent($style['id']);
            $style_row_nodes['style_type']->setContent(strtoupper($style['style_type']));
            $style_row_nodes['name']->setContent($style['name']);
            $style_row_nodes['directory']->setContent($style['directory']);

            if (in_array($style['id'], $installed_ids))
            {
                $style_row_nodes['install-link']->remove();
                $style_row_nodes['action-1']->setContent(_gettext('Style Installed'));
            }
            else
            {
                $install_link = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'styles', 'action' => 'add', 'style-id' => $style['id'],
                        'style-type' => $style['style_type']]);
                        $style_row_nodes['install-link']->extSetAttribute('href', $install_link);
            }
        }

        $available_style_list_nodes['style-row']->remove();
        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}