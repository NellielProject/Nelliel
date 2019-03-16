<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

class OutputPanelBoard extends OutputCore
{
    private $database;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $this->prepare('management/panels/board_panel.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Options')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $manage_options = $this->dom->getElementById('manage-options');
        $settings = $this->dom->getElementById('module-board-settings');
        $session = new \Nelliel\Session(true);
        $user = $session->sessionUser();

        if ($user->domainPermission($this->domain, 'perm_board_config_access'))
        {
            $settings_elements = $settings->getElementsByAttributeName('data-parse-id', true);
            $settings_elements['board-settings-link']->extSetAttribute('href',
                    MAIN_SCRIPT . '?module=board-settings&board_id=' . $this->domain->id());
        }
        else
        {
            $settings->remove();
        }

        $bans = $this->dom->getElementById('module-bans');

        if ($user->domainPermission($this->domain, 'perm_ban_access'))
        {
            $bans_elements = $bans->getElementsByAttributeName('data-parse-id', true);
            $bans_elements['bans-link']->extSetAttribute('href', MAIN_SCRIPT . '?module=bans&board_id=' . $this->domain->id());
        }
        else
        {
            $bans->remove();
        }

        $threads = $this->dom->getElementById('module-threads');

        if ($user->domainPermission($this->domain, 'perm_threads_access'))
        {
            $threads_elements = $threads->getElementsByAttributeName('data-parse-id', true);
            $threads_elements['threads-link']->extSetAttribute('href',
                    MAIN_SCRIPT . '?module=threads-admin&board_id=' . $this->domain->id());
        }
        else
        {
            $threads->remove();
        }

        $modmode = $this->dom->getElementById('module-modmode');

        if ($user->domainPermission($this->domain, 'perm_modmode_access'))
        {
            $modmode_elements = $modmode->getElementsByAttributeName('data-parse-id', true);
            $modmode_elements['modmode-link']->extSetAttribute('href',
                    MAIN_SCRIPT . '?module=render&action=view-index&index=0&board_id=' . $this->domain->id() . '&modmode=true');
        }
        else
        {
            $modmode->remove();
        }

        $reports = $this->dom->getElementById('module-reports');

        if ($user->domainPermission($this->domain, 'perm_reports_access'))
        {
            $reports_elements = $reports->getElementsByAttributeName('data-parse-id', true);
            $reports_elements['reports-link']->extSetAttribute('href',
                    MAIN_SCRIPT . '?module=reports&board_id=' . $this->domain->id());
        }
        else
        {
            $reports->remove();
        }

        $file_filters = $this->dom->getElementById('module-file-filters');

        if ($user->domainPermission($this->domain, 'perm_file_filters_access'))
        {
            $file_filters_elements = $file_filters->getElementsByAttributeName('data-parse-id', true);
            $file_filters_elements['file-filters-link']->extSetAttribute('href',
                    MAIN_SCRIPT . '?module=file-filters&board_id=' . $this->domain->id());
        }
        else
        {
            $file_filters->remove();
        }

        if ($user->domainPermission($this->domain, 'perm_regen_pages'))
        {
            $this->dom->getElementById('regen-all-pages')->extSetAttribute('href',
                    MAIN_SCRIPT . '?module=regen&action=board-all-pages&board_id=' . $this->domain->id());
        }
        else
        {
            $this->dom->getElementById('regen-all-pages')->parentNode->remove();
        }

        if ($user->domainPermission($this->domain, 'perm_regen_cache'))
        {
            $this->dom->getElementById('regen-all-caches')->extSetAttribute('href',
                    MAIN_SCRIPT . '?module=regen&action=board-all-caches&board_id=' . $this->domain->id());
        }
        else
        {
            $this->dom->getElementById('regen-all-caches')->parentNode->remove();
        }

        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}