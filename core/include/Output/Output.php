<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Timer;
use Nelliel\Account\Session;
use Nelliel\Database\NellielPDO;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainSite;
use Nelliel\Render\RenderCoreDOM;
use Nelliel\Render\RenderCoreMustache;
use Nelliel\Render\Template;
use Nelliel\Utility\FileHandler;
use Nelliel\Domains\DomainGlobal;

abstract class Output
{
    protected Domain $domain;
    protected DomainSite $site_domain;
    protected DomainGlobal $global_domain;
    protected NellielPDO $database;
    protected $render_core;
    protected array $render_data = array();
    protected static $render_cores = array();
    protected FileHandler $file_handler;
    protected Filter $output_filter;
    protected string $core_id;
    protected bool $write_mode = false;
    protected Session $session;
    protected $default_body_template = 'empty_body';
    protected Timer $timer;
    protected string $templates_path;
    protected Template $current_template;

    function __construct(Domain $domain, bool $write_mode)
    {
        $this->domain = $domain;
        $this->write_mode = $write_mode;
        $this->database = $domain->database();
        $this->selectRenderCore('mustache');
        $this->site_domain = nel_site_domain();
        $this->global_domain = nel_global_domain();
        $this->file_handler = nel_utilities()->fileHandler();
        $this->output_filter = new Filter();
        $this->session = new Session();
        $this->templates_path = $this->domain->templatePath();
        $this->timer = new Timer();
    }

    protected function renderSetup(): void
    {
        $this->render_data = array();
        $this->timer->reset();
        $this->timer->start();
        $this->render_data['show_stats']['render_timer'] = $this->timerTotalFunction(true, 4);
        $this->render_data['show_render_time'] = $this->domain->setting('show_render_timer');
        $this->render_data['page_language'] = $this->domain->locale(true);
        $this->render_data['nelliel_version'] = NELLIEL_VERSION;
        $this->render_data['nelliel_package'] = NELLIEL_PACKAGE;
        $this->render_data['nelliel_copyright_dates'] = NELLIEL_COPYRIGHT_DATES;
        $this->render_data['nelliel_copyright_line'] = NELLIEL_COPYRIGHT_LINE;
        $this->render_data['preview_loading'] = $this->domain->setting('preview_lazy_loading') ? 'lazy' : 'eager';
        $this->uiDefines();
    }

    protected function selectRenderCore(string $core_id): void
    {
        if ($core_id === 'mustache') {
            self::$render_cores['mustache'] = self::$render_cores['mustache'] ?? new RenderCoreMustache($this->domain);
            $this->render_core = self::$render_cores['mustache'];
        } else if ($core_id === 'DOM') {
            self::$render_cores['DOM'] = self::$render_cores['DOM'] ?? new RenderCoreDOM($this->file_hander);
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

    protected function output(string $template, bool $data_only, bool $translate, array $render_data, $dom = null)
    {
        $output = null;

        if ($this->core_id === 'mustache') {
            $this->render_core->renderEngine()->getLoader()->setDefaultBasePath($this->templates_path);

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

    protected function setBodyTemplate(string $name): void
    {
        // Temporary values while we get things working
        $template = new Template($this->templates_path, $name, '.html');
        $this->render_core->renderEngine()->getLoader()->addSubstitute($this->default_body_template, $template);
    }

    protected function setTemplate(Template $template): void
    {
        $this->current_template = $template;
    }

    protected function getUIText(string $id) {
        $domain = $this->domain;
        $do_translation = $domain->setting('translate_mod_links');

        $translate = function (string $setting) use ($domain, $do_translation) {
            $value = $domain->setting($setting) ?? '';

            if ($do_translation && $value !== '') {
                $value = __($value);
            }

            return $value;
        };

        return $translate($id);
    }

    protected function uiDefines(): void
    {
        if ($this->domain->id() === Domain::SITE) {
            return;
        }

        $domain = $this->domain;
        $do_translation = $domain->setting('translate_content_links');

        $translate = function (string $setting) use ($domain, $do_translation) {
            $value = $domain->setting($setting) ?? '';

            if ($do_translation && $value !== '') {
                $value = __($value);
            }

            return $value;
        };

        $left_bracket = $translate('content_links_left_bracket');
        $right_bracket = $translate('content_links_right_bracket');
        $content_links = array();
        $content_links['content_links_reply']['text'] = $translate('content_links_reply');
        $content_links['content_links_hide_thread']['text'] = $translate('content_links_hide_thread');
        $content_links['content_links_hide_thread']['command'] = 'hide-thread';
        $content_links['content_links_hide_thread']['alt_command'] = 'show-thread';
        $content_links['content_links_hide_thread']['alt_text'] = $translate('content_links_show_thread');
        $content_links['content_links_hide_post']['text'] = $translate('content_links_hide_post');
        $content_links['content_links_hide_post']['command'] = 'hide-post';
        $content_links['content_links_hide_post']['alt_command'] = 'show-post';
        $content_links['content_links_hide_post']['alt_text'] = $translate('content_links_show_post');
        $content_links['content_links_hide_file']['text'] = $translate('content_links_hide_file');
        $content_links['content_links_hide_file']['command'] = 'hide-file';
        $content_links['content_links_hide_file']['alt_command'] = 'show-file';
        $content_links['content_links_hide_file']['alt_text'] = $translate('content_links_show_file');
        $content_links['content_links_hide_embed']['text'] = $translate('content_links_hide_embed');
        $content_links['content_links_hide_embed']['command'] = 'hide-embed';
        $content_links['content_links_hide_embed']['alt_command'] = 'show-embed';
        $content_links['content_links_hide_embed']['alt_text'] = $translate('content_links_show_embed');
        $content_links['content_links_cite_post']['text'] = $translate('content_links_cite_post');
        $content_links['content_links_cite_post']['command'] = 'cite-post';
        $content_links['content_links_show_upload_meta']['text'] = $translate('content_links_show_upload_meta');
        $content_links['content_links_show_upload_meta']['command'] = 'show-upload-meta';
        $content_links['content_links_show_upload_meta']['alt_command'] = 'hide-upload-meta';
        $content_links['content_links_show_upload_meta']['alt_text'] = $translate('content_links_hide_upload_meta');
        $content_links['content_links_download_file']['text'] = $translate('content_links_download_file');
        $content_links['content_links_first_posts']['text'] = $translate('content_links_first_posts');
        $content_links['content_links_last_posts']['text'] = $translate('content_links_last_posts');
        $content_links['content_links_expand_thread']['text'] = $translate('content_links_expand_thread');
        $content_links['content_links_expand_thread']['command'] = 'expand-thread';
        $content_links['content_links_expand_thread']['alt_command'] = 'collapse-thread';
        $content_links['content_links_expand_thread']['alt_text'] = $translate('content_links_collapse_thread');

        foreach ($content_links as $id => $values) {
            $this->render_data[$id]['left_bracket'] = $left_bracket;
            $this->render_data[$id]['right_bracket'] = $right_bracket;
            $this->render_data[$id] = array_merge($this->render_data[$id], $values);
        }
    }
}
