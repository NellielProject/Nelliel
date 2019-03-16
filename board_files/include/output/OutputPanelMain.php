<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelMain extends OutputCore
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
        $this->prepare('management/panels/main_panel.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Options')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $board_entry = $this->dom->getElementById('board-entry');
        $insert_before = $board_entry->parentNode->lastChild;
        $boards = $this->database->executeFetchAll('SELECT * FROM "' . BOARD_DATA_TABLE . '"', PDO::FETCH_ASSOC);

        if ($boards !== false)
        {
            foreach ($boards as $board)
            {
                $entry = $board_entry->cloneNode(true);
                $board_entry->parentNode->insertBefore($entry, $insert_before);
                $entry->removeAttribute('id');
                $entry_elements = $entry->getElementsByAttributeName('data-parse-id', true);
                $entry_elements['board-link']->extSetAttribute('href',
                        MAIN_SCRIPT . '?module=main-panel&board_id=' . $board['board_id']);
                $entry_elements['board-link']->extSetAttribute('title', $board['board_id']);
                $entry_elements['board-link']->setContent('/' . $board['board_id'] . '/');
            }
        }

        $board_entry->remove();
        $manage_options = $this->dom->getElementById('manage-options');
        $manage_options_nodes = $manage_options->getElementsByAttributeName('data-parse-id', true);

        if ($user->domainPermission($this->domain, 'perm_manage_boards_access'))
        {
            $manage_options_nodes['module-link-manage-boards']->extSetAttribute('href', MAIN_SCRIPT . '?module=manage-boards');
        }
        else
        {
            $manage_options_nodes['module-link-manage-boards']->remove();
        }

        if ($user->domainPermission($this->domain, 'perm_user_access'))
        {
            $manage_options_nodes['module-link-users']->extSetAttribute('href', MAIN_SCRIPT . '?module=users');
        }
        else
        {
            $manage_options_nodes['module-link-users']->remove();
        }

        if ($user->domainPermission($this->domain, 'perm_role_access'))
        {
            $manage_options_nodes['module-link-roles']->extSetAttribute('href', MAIN_SCRIPT . '?module=roles');
        }
        else
        {
            $manage_options_nodes['module-link-roles']->remove();
        }

        if ($user->domainPermission($this->domain, 'perm_site_config_access'))
        {
            $manage_options_nodes['module-link-site-settings']->extSetAttribute('href', MAIN_SCRIPT . '?module=site-settings');
        }
        else
        {
            $manage_options_nodes['module-link-site-settings']->remove();
        }

        if ($user->domainPermission($this->domain, 'perm_file_filters_access'))
        {
            $manage_options_nodes['module-link-file-filters']->extSetAttribute('href', MAIN_SCRIPT . '?module=file-filters');
        }
        else
        {
            $manage_options_nodes['module-link-file-filters']->remove();
        }

        if ($user->domainPermission($this->domain, 'perm_board_defaults_access'))
        {
            $manage_options_nodes['module-link-board-defaults']->extSetAttribute('href',
                    MAIN_SCRIPT . '?module=default-board-settings');
        }
        else
        {
            $manage_options_nodes['module-link-board-defaults']->remove();
        }

        if ($user->domainPermission($this->domain, 'perm_reports_access'))
        {
            $manage_options_nodes['module-link-reports']->extSetAttribute('href', MAIN_SCRIPT . '?module=reports');
        }
        else
        {
            $manage_options_nodes['module-link-reports']->remove();
        }

        if ($user->domainPermission($this->domain, 'perm_templates_access'))
        {
            $manage_options_nodes['module-link-templates']->extSetAttribute('href', MAIN_SCRIPT . '?module=templates');
        }
        else
        {
            $manage_options_nodes['module-link-templates']->remove();
        }

        if ($user->domainPermission($this->domain, 'perm_filetypes_access'))
        {
            $manage_options_nodes['module-link-filetypes']->extSetAttribute('href', MAIN_SCRIPT . '?module=filetypes');
        }
        else
        {
            $manage_options_nodes['module-link-filetypes']->remove();
        }

        if ($user->domainPermission($this->domain, 'perm_styles_access'))
        {
            $manage_options_nodes['module-link-styles']->extSetAttribute('href', MAIN_SCRIPT . '?module=styles');
        }
        else
        {
            $manage_options_nodes['module-link-styles']->remove();
        }

        if ($user->domainPermission($this->domain, 'perm_permissions_access'))
        {
            $manage_options_nodes['module-link-permissions']->extSetAttribute('href', MAIN_SCRIPT . '?module=permissions');
        }
        else
        {
            $manage_options_nodes['module-link-permissions']->remove();
        }

        if ($user->domainPermission($this->domain, 'perm_icon_sets_access'))
        {
            $manage_options_nodes['module-link-icon-sets']->extSetAttribute('href', MAIN_SCRIPT . '?module=icon-sets');
        }
        else
        {
            $manage_options_nodes['module-link-icon-sets']->remove();
        }

        if ($user->domainPermission($this->domain, 'perm_news_access'))
        {
            $manage_options_nodes['module-link-news']->extSetAttribute('href', MAIN_SCRIPT . '?module=news');
        }
        else
        {
            $manage_options_nodes['module-link-news']->remove();
        }

        if ($user->domainPermission($this->domain, 'perm_extract_gettext'))
        {
            $manage_options_nodes['module-extract-gettext']->extSetAttribute('href',
                    MAIN_SCRIPT . '?module=language&action=extract-gettext');
        }
        else
        {
            $manage_options_nodes['module-extract-gettext']->remove();
        }

        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}