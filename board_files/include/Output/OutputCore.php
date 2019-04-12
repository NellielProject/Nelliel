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
    protected $database;
    protected $render_core;
    protected $render_data = array();
    protected $file_handler;
    protected $cache_handler;
    protected $output_filter;
    protected $url_constructor;
    protected $timer_start;
    protected $core_id;

    public abstract function render(array $parameters, bool $data_only);

    protected function utilitySetup()
    {
        $this->file_handler = new \Nelliel\FileHandler();
        $this->cache_handler = new \Nelliel\CacheHandler();
        $this->output_filter = new \Nelliel\OutputFilter();
        $this->url_constructor = new \Nelliel\URLConstructor();
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

    protected function startTimer()
    {
        $start = microtime(true);
        $this->timer_start = $start;
        return $start;
    }

    protected function endTimer()
    {
        if (!isset($this->timer_start))
        {
            return 0;
        }

        $end_time = microtime(true);
        return round($end_time - $this->timer_start, 4);
    }

    protected function output(string $template, bool $data_only, bool $translate, array $render_data = array(), $dom = null)
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
                if($this->domain->setting('display_render_timer') && isset($this->timer_start))
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
}