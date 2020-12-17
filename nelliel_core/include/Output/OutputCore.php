<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class OutputCore
{
    protected $dom;
    protected $domain;
    protected $site_domain;
    protected $database;
    protected $render_core;
    protected $render_data = array();
    protected $file_handler;
    protected $cache_handler;
    protected $output_filter;
    protected $timer_start = 0;
    protected $timer_end = 0;
    protected $core_id;
    protected $static_output = false;
    protected $write_mode = false;

    public abstract function render(array $parameters, bool $data_only);

    // Standard setup when beginning a render
    protected function renderSetup()
    {
        $this->render_data = array();
        $this->startTimer(); // Begin rendering timer
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale()); // Convert underscore notation to hyphen for HTML
    }

    protected function utilitySetup()
    {
        $this->site_domain = new \Nelliel\DomainSite(nel_database());
        $this->file_handler = new \Nelliel\Utility\FileHandler();
        $this->output_filter = new \Nelliel\OutputFilter();
    }

    protected function selectRenderCore(string $core_id)
    {
        $this->core_id = $core_id;

        if ($core_id === 'mustache')
        {
            $this->render_core = new \Nelliel\RenderCoreMustache($this->domain);
        }
        else if ($core_id === 'DOM')
        {
            $this->render_core = new \Nelliel\RenderCoreDOM();
        }
        else
        {
            ;
        }
    }

    protected function startTimer(int $time_offset = 0)
    {
        $start = microtime(true);
        $this->timer_start = $start - $time_offset;
        return $start;
    }

    protected function endTimer(bool $rounded = true, int $precision = 4)
    {
        $this->timer_end = microtime(true);

        if ($rounded)
        {
            return number_format($this->timer_end - $this->timer_start, $precision);
        }
        else
        {
            return $this->timer_end - $this->timer_start;
        }
    }

    protected function output(string $template, bool $data_only, bool $translate, array $render_data = array(),
            $dom = null)
    {
        $output = null;
        $render_data = (empty($render_data)) ? $this->render_data : $render_data;
        $dom = (is_null($dom)) ? $this->dom : $dom;

        if ($this->core_id === 'mustache')
        {
            if ($data_only)
            {
                $output = $render_data;
            }
            else
            {
                if ($this->domain->setting('display_render_timer') && isset($this->timer_start))
                {
                    $render_data['show_stats']['render_timer'] = function ()
                    {
                        return 'Page rendered in ' . $this->endTimer() . ' seconds.';
                    };
                }

                $output = $this->render_core->renderFromTemplateFile($template, $render_data);

                if ($translate)
                {
                    $output = $this->domain->translator()->translateHTML($output);
                }
            }
        }

        return $output;
    }

    public function staticOutput(bool $status = null)
    {
        if (!is_null($status))
        {
            $this->static_output = $status;
        }

        return $this->static_output;
    }

    public function writeMode(bool $status = null)
    {
        if (!is_null($status))
        {
            $this->write_mode = $status;
        }

        return $this->write_mode;
    }
}