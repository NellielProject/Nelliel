<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class OutputCore
{
    protected $domain;
    protected $database;
    protected $render_core;
    protected $file_handler;
    protected $cache_handler;
    protected $output_filter;
    protected $url_constructor;
    protected $timer_start;

    public abstract function render(array $parameters = array());

    protected function utilitySetup()
    {
        $this->file_handler = new \Nelliel\FileHandler();
        $this->cache_handler = new \Nelliel\CacheHandler();
        $this->output_filter = new \Nelliel\OutputFilter();
        $this->url_constructor = new \Nelliel\URLConstructor();
    }

    protected function selectRenderCore(string $core_id)
    {
        if($core_id === 'mustache')
        {
            $this->render_core = new \Nelliel\RenderCoreMustache($this->domain);
        }
        else if($core_id === 'DOM')
        {
            $this->render_core = new \Nelliel\RenderCoreDOM();
        }
        else
        {
            ;
        }
    }

    public function startTimer()
    {
        $start = microtime(true);
        $this->timer_start = $start;
        return $start;
    }

    public function endTimer()
    {
        if (!isset($this->timer_start))
        {
            return 0;
        }

        $end_time = microtime(true);
        return $end_time - $this->timer_start;
    }

    public function translate()
    {

    }
}