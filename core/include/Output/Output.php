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
    protected $templates_path;

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
        $this->templates_path = $this->domain->templatePath();
    }

    protected function renderSetup(): void
    {
        $this->render_data = array();
        $this->render_data['page_language'] = $this->domain->locale();
        $this->uiDefines();
    }

    protected function selectRenderCore(string $core_id): void
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

    protected function setupTimer(bool $formatted = true, int $precision = 4): void
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
            $this->render_core->renderEngine()->getLoader()->setTemplatePath($this->templates_path);

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

    public function writeMode(bool $status = null): bool
    {
        if (!is_null($status)) {
            $this->write_mode = $status;
        }

        return $this->write_mode;
    }

    protected function setTemplatesPath(string $path): void
    {
        $this->templates_path = $path;
    }

    protected function setBodyTemplate(string $template): void
    {
        $this->template_substitutes->add($this->default_body_template, $template);
    }

    protected function uiDefines(): void
    {
        if ($this->domain->id() === Domain::SITE) {
            return;
        }

        $left_bracket = $this->getUISetting('mod_links_left_bracket');
        $right_bracket = $this->getUISetting('mod_links_right_bracket');
        $mod_links = array();
        $mod_links['mod_links_lock']['text'] = $this->getUISetting('mod_links_lock');
        $mod_links['mod_links_unlock']['text'] = $this->getUISetting('mod_links_unlock');
        $mod_links['mod_links_sticky']['text'] = $this->getUISetting('mod_links_sticky');
        $mod_links['mod_links_unsticky']['text'] = $this->getUISetting('mod_links_unsticky');
        $mod_links['mod_links_permasage']['text'] = $this->getUISetting('mod_links_permasage');
        $mod_links['mod_links_unpermasage']['text'] = $this->getUISetting('mod_links_unpermasage');
        $mod_links['mod_links_cyclic']['text'] = $this->getUISetting('mod_links_cyclic');
        $mod_links['mod_links_non_cyclic']['text'] = $this->getUISetting('mod_links_non_cyclic');
        $mod_links['mod_links_ban']['text'] = $this->getUISetting('mod_links_ban');
        $mod_links['mod_links_delete']['text'] = $this->getUISetting('mod_links_delete');
        $mod_links['mod_links_delete_by_ip']['text'] = $this->getUISetting('mod_links_delete_by_ip');
        $mod_links['mod_links_global_delete_by_ip']['text'] = $this->getUISetting('mod_links_global_delete_by_ip');
        $mod_links['mod_links_ban_and_delete']['text'] = $this->getUISetting('mod_links_ban_and_delete');
        $mod_links['mod_links_edit']['text'] = $this->getUISetting('mod_links_edit');
        $mod_links['mod_links_move']['text'] = $this->getUISetting('mod_links_move');
        $mod_links['mod_links_merge']['text'] = $this->getUISetting('mod_links_merge');
        $mod_links['mod_links_spoiler']['text'] = $this->getUISetting('mod_links_spoiler');
        $mod_links['mod_links_unspoiler']['text'] = $this->getUISetting('mod_links_unspoiler');

        foreach ($mod_links as $id => $values) {
            $this->render_data[$id]['left_bracket'] = $left_bracket;
            $this->render_data[$id]['right_bracket'] = $right_bracket;
            $this->render_data[$id] = array_merge($this->render_data[$id], $values);
        }

        $left_bracket = $this->getUISetting('content_links_left_bracket');
        $right_bracket = $this->getUISetting('content_links_right_bracket');
        $content_links = array();
        $content_links['content_links_reply']['text'] = $this->getUISetting('content_links_reply');

        $content_links['content_links_hide_thread']['text'] = $this->getUISetting('content_links_hide_thread');
        $content_links['content_links_hide_thread']['command'] = 'hide-thread';
        $content_links['content_links_hide_thread']['alt_command'] = 'show-thread';
        $content_links['content_links_hide_thread']['alt_text'] = $this->getUISetting('content_links_show_thread');
        $content_links['content_links_hide_post']['text'] = $this->getUISetting('content_links_hide_post');
        $content_links['content_links_hide_post']['command'] = 'hide-post';
        $content_links['content_links_hide_post']['alt_command'] = 'show-post';
        $content_links['content_links_hide_post']['alt_text'] = $this->getUISetting('content_links_show_post');
        $content_links['content_links_hide_file']['text'] = $this->getUISetting('content_links_hide_file');
        $content_links['content_links_hide_file']['command'] = 'hide-file';
        $content_links['content_links_hide_file']['alt_command'] = 'show-file';
        $content_links['content_links_hide_file']['alt_text'] = $this->getUISetting('content_links_show_file');
        $content_links['content_links_hide_embed']['text'] = $this->getUISetting('content_links_hide_embed');
        $content_links['content_links_hide_embed']['command'] = 'hide-embed';
        $content_links['content_links_hide_embed']['alt_command'] = 'show-embed';
        $content_links['content_links_hide_embed']['alt_text'] = $this->getUISetting('content_links_show_embed');
        $content_links['content_links_cite_post']['text'] = $this->getUISetting('content_links_cite_post');
        $content_links['content_links_cite_post']['command'] = 'cite-post';
        $content_links['content_links_show_upload_meta']['text'] = $this->getUISetting('content_links_show_upload_meta');
        $content_links['content_links_show_upload_meta']['command'] = 'show-upload-meta';
        $content_links['content_links_show_upload_meta']['alt_command'] = 'hide-upload-meta';
        $content_links['content_links_show_upload_meta']['alt_text'] = $this->getUISetting('content_links_hide_upload_meta');
        $content_links['content_links_download_file']['text'] = $this->getUISetting('content_links_download_file');
        $content_links['content_links_first_posts']['text'] = $this->getUISetting('content_links_first_posts');
        $content_links['content_links_last_posts']['text'] = $this->getUISetting('content_links_last_posts');

        $content_links['content_links_expand_thread']['text'] = $this->getUISetting('content_links_expand_thread');
        $content_links['content_links_expand_thread']['command'] = 'expand-thread';
        $content_links['content_links_expand_thread']['alt_command'] = 'collapse-thread';
        $content_links['content_links_expand_thread']['alt_text'] = $this->getUISetting('content_links_collapse_thread');

        foreach ($content_links as $id => $values) {
            $this->render_data[$id]['left_bracket'] = $left_bracket;
            $this->render_data[$id]['right_bracket'] = $right_bracket;
            $this->render_data[$id] = array_merge($this->render_data[$id], $values);
        }
    }

    protected function getUISetting(string $setting)
    {
        $value = $this->domain->setting($setting) ?? '';

        if ($value !== '') {
            return __($value);
        }

        return $value;
    }
}
