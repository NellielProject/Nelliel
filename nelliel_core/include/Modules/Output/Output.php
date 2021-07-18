<?php

declare(strict_types=1);

namespace Nelliel\Modules\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Timer;
use Nelliel\Domains\Domain;
use Nelliel\Render\Filter;
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
        $this->session = new \Nelliel\Modules\Account\Session();

        if($this->session->modmodeRequested())
        {
            $this->session->init(true);
        }
    }

    protected function renderSetup()
    {
        $this->render_data = array();
        $this->render_data['page_language'] = $this->domain->locale();
    }

    protected function selectRenderCore(string $core_id)
    {
        if ($core_id === 'mustache')
        {
            self::$render_cores['mustache'] = self::$render_cores['mustache'] ?? new RenderCoreMustache($this->domain);
            $this->render_core = self::$render_cores['mustache'];
        }
        else if ($core_id === 'DOM')
        {
            self::$render_cores['DOM'] = self::$render_cores['DOM'] ?? new RenderCoreDOM();
            $this->render_core = self::$render_cores['DOM'];
        }
        else
        {
            return;
        }

        $this->core_id = $core_id;
    }

    protected function timerTotalFunction(bool $rounded = true, int $precision = 4)
    {
        return function () use ($rounded, $precision)
        {
            return $this->timer->elapsed($rounded, $precision);
        };
    }

    protected function setupTimer(bool $rounded = true, int $precision = 4)
    {
        if ($this->domain->setting('display_render_timer'))
        {
            $this->timer = new Timer();
            $this->timer->start();
            $this->render_data['show_stats']['render_timer'] = $this->timerTotalFunction($rounded, $precision);
        }
    }

    protected function output(string $template, bool $data_only, bool $translate, array $render_data, $dom = null)
    {
        $output = null;
        $substitutes = $this->template_substitutes->getAll();

        if ($this->core_id === 'mustache')
        {
            $this->render_core->renderEngine()->getLoader()->updateSubstituteTemplates($substitutes);

            if ($data_only)
            {
                return $render_data;
            }
            else
            {
                $output = $this->render_core->renderFromTemplateFile($template, $render_data);

                if ($translate)
                {
                    $output = $this->domain->translator()->translateHTML($output);
                }
            }
        }

        return $output;
    }

    public function writeMode(bool $status = null)
    {
        if (!is_null($status))
        {
            $this->write_mode = $status;
        }

        return $this->write_mode;
    }

    protected function setBodyTemplate(string $template)
    {
        $this->template_substitutes->add($this->default_body_template, $template);
    }
}
