<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Timer;
use Nelliel\Account\Session;
use Nelliel\Domains\Domain;
use Nelliel\Render\RenderCoreDOM;
use Nelliel\Render\RenderCoreMustache;
use Nelliel\Render\TemplateSubstitutes;

abstract class Output
{
    protected $domain;
    protected $site_domain;
    protected $database;
    protected $render_core;
    protected $render_data = array();
    protected static $render_cores = array();
    protected $file_handler;
    protected $output_filter;
    protected $core_id;
    protected $static_output = false;
    protected $write_mode = false;
    protected $template_substitutes;
    protected $session;
    protected $default_body_template = 'empty_body';
    protected $timer;

    function __construct(Domain $domain, bool $write_mode)
    {
        $this->domain = $domain;
        $this->write_mode = $write_mode;
        $this->database = $domain->database();
        $this->selectRenderCore('mustache');
        $this->site_domain = nel_site_domain();
        $this->file_handler = nel_utilities()->fileHandler();
        $this->output_filter = new Filter();
        $this->template_substitutes = new TemplateSubstitutes();
        $this->session = new Session();
    }

    protected function renderSetup()
    {
        $this->render_data = array();
        $this->render_data['page_language'] = $this->domain->locale();
        $this->uiDefines();
    }

    protected function selectRenderCore(string $core_id)
    {
        if ($core_id === 'mustache') {
            self::$render_cores['mustache'] = self::$render_cores['mustache'] ?? new RenderCoreMustache($this->domain);
            $this->render_core = self::$render_cores['mustache'];
        } else if ($core_id === 'DOM') {
            self::$render_cores['DOM'] = self::$render_cores['DOM'] ?? new RenderCoreDOM();
            $this->render_core = self::$render_cores['DOM'];
        } else {
            return;
        }

        $this->core_id = $core_id;
    }

    protected function timerTotalFunction(bool $formatted = true, int $precision = 4)
    {
        return function () use ($formatted, $precision) {
            if ($formatted) {
                return $this->timer->elapsedFormatted($precision);
            } else {
                return $this->timer->elapsed();
            }
        };
    }

    protected function setupTimer(bool $formatted = true, int $precision = 4)
    {
        if ($this->domain->setting('show_render_timer')) {
            $this->timer = new Timer();
            $this->timer->start();
            $this->render_data['show_stats']['render_timer'] = $this->timerTotalFunction($formatted, $precision);
        }
    }

    protected function output(string $template, bool $data_only, bool $translate, array $render_data, $dom = null)
    {
        $output = null;
        $substitutes = $this->template_substitutes->getAll();

        if ($this->core_id === 'mustache') {
            $this->render_core->renderEngine()->getLoader()->updateSubstituteTemplates($substitutes);

            if ($data_only) {
                return $render_data;
            } else {
                $output = $this->render_core->renderFromTemplateFile($template, $render_data);

                if ($translate) {
                    $output = $this->domain->translator()->translateHTML($output);
                }
            }
        }

        return $output;
    }

    public function writeMode(bool $status = null)
    {
        if (!is_null($status)) {
            $this->write_mode = $status;
        }

        return $this->write_mode;
    }

    protected function setBodyTemplate(string $template)
    {
        $this->template_substitutes->add($this->default_body_template, $template);
    }

    protected function uiDefines(): void
    {
        if ($this->domain->id() === Domain::SITE) {
            return;
        }

        $this->render_data['mod_links_delimiter_left'] = $this->getUISetting('mod_links_delimiter_left');
        $this->render_data['mod_links_delimiter_right'] = $this->getUISetting('mod_links_delimiter_right');
        $this->render_data['mod_links_lock'] = $this->getUISetting('mod_links_lock');
        $this->render_data['mod_links_unlock'] = $this->getUISetting('mod_links_unlock');
        $this->render_data['mod_links_sticky'] = $this->getUISetting('mod_links_sticky');
        $this->render_data['mod_links_unsticky'] = $this->getUISetting('mod_links_unsticky');
        $this->render_data['mod_links_permasage'] = $this->getUISetting('mod_links_permasage');
        $this->render_data['mod_links_unpermasage'] = $this->getUISetting('mod_links_unpermasage');
        $this->render_data['mod_links_cyclic'] = $this->getUISetting('mod_links_cyclic');
        $this->render_data['mod_links_non_cyclic'] = $this->getUISetting('mod_links_non_cyclic');
        $this->render_data['mod_links_ban'] = $this->getUISetting('mod_links_ban');
        $this->render_data['mod_links_delete'] = $this->getUISetting('mod_links_delete');
        $this->render_data['mod_links_delete_by_ip'] = $this->getUISetting('mod_links_delete_by_ip');
        $this->render_data['mod_links_global_delete_by_ip'] = $this->getUISetting('mod_links_global_delete_by_ip');
        $this->render_data['mod_links_ban_and_delete'] = $this->getUISetting('mod_links_ban_and_delete');
        $this->render_data['mod_links_edit'] = $this->getUISetting('mod_links_edit');
        $this->render_data['mod_links_move'] = $this->getUISetting('mod_links_move');
        $this->render_data['mod_links_spoiler'] = $this->getUISetting('mod_links_spoiler');
        $this->render_data['mod_links_unspoiler'] = $this->getUISetting('mod_links_unspoiler');

        if ($this->session->inModmode($this->domain)) {
            $this->render_data['mod_can_lock'] = $this->session->user()->checkPermission($this->domain,
                'perm_modify_content_status');
            $this->render_data['mod_can_sticky'] = $this->session->user()->checkPermission($this->domain,
                'perm_modify_content_status');
            $this->render_data['mod_can_permasage'] = $this->session->user()->checkPermission($this->domain,
                'perm_modify_content_status');
            $this->render_data['mod_can_cyclic'] = $this->session->user()->checkPermission($this->domain,
                'perm_post_content_status');
            $this->render_data['mod_can_ban'] = $this->session->user()->checkPermission($this->domain,
                'perm_manage_bans');
            $this->render_data['mod_can_delete'] = $this->session->user()->checkPermission($this->domain,
                'perm_delete_content');
            $this->render_data['mod_can_by_ip'] = $this->session->user()->checkPermission($this->domain,
                'perm_delete_by_ip');
            $this->render_data['mod_can_global_by_ip'] = $this->session->user()->checkPermission(nel_global_domain(),
                'perm_delete_by_ip');
            $this->render_data['mod_can_ban_delete'] = $this->session->user()->checkPermission($this->domain,
                'perm_manage_bans') && $this->session->user()->checkPermission($this->domain, 'perm_delete_content');
            $this->render_data['mod_can_edit'] = $this->session->user()->checkPermission($this->domain,
                'perm_edit_posts');
            $this->render_data['mod_can_move'] = $this->session->user()->checkPermission($this->domain,
                'perm_move_content');
            $this->render_data['mod_can_spoiler'] = $this->session->user()->checkPermission($this->domain,
                'perm_modify_content_status');
        }
    }

    protected function getUISetting(string $setting)
    {
        $value = $this->domain->setting($setting) ?? '';

        if ($value !== '') {
            return _gettext($value);
        }

        return $value;
    }
}
