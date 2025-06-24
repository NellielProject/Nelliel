<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Timer;
use Nelliel\Account\Session;
use Nelliel\Database\NellielPDO;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainGlobal;
use Nelliel\Domains\DomainSite;
use Nelliel\Render\RenderCoreDOM;
use Nelliel\Render\RenderCoreMustache;
use Nelliel\Render\Template;
use Nelliel\Utility\FileHandler;

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
        $this->site_domain = nel_get_cached_domain(Domain::SITE);
        $this->global_domain = nel_get_cached_domain(Domain::GLOBAL);
        $this->file_handler = nel_utilities()->fileHandler();
        $this->output_filter = new Filter();
        $this->session = new Session();
        $this->templates_path = $this->domain->templatePath();
        $this->timer = new Timer();
    }

    protected function renderSetup(string $base_path = null, string $extension = null): void
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
        $this->setBasePath($base_path ?? $this->templates_path);
        $this->setExtension($extension ?? '.html');
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

    protected function setBasePath(string $base_path): void
    {
        $this->render_core->renderEngine()->getLoader()->setBasePath($base_path);
    }

    protected function setExtension(string $extension): void
    {
        $this->render_core->renderEngine()->getLoader()->setExtension($extension);
    }
}
