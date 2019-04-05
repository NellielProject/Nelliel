<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class RenderCore
{
    protected $output_sets;
    protected $output_filter;
    protected $file_handler;
    protected $template_loaders;
    protected $template_path;

    public abstract function loadTemplateFromFile(string $file);

    public abstract function renderFromTemplateFile(string $file, array $render_data);

    public function getVersion()
    {
        return $this->version;
    }

    public function clearOutput(string $output_id = 'default')
    {
        $this->output_sets[$output_id]['content'] = '';
    }

    public function createOutput(string $output_id = 'default')
    {
        if (!isset($this->output_sets[$output_id]))
        {
            $this->output_sets[$output_id]['content'] = '';
        }
    }

    public function appendToOutput(string $input, string $output_id = 'default')
    {
        $this->createOutput($output_id);
        $this->output_sets[$output_id]['content'] .= $input;
    }

    public function getOutput(string $output_id = 'default')
    {
        return $this->output_sets[$output_id]['content'];
    }

    public function startTimer(string $output_id = 'default')
    {
        $start = microtime(true);
        $this->output_sets[$output_id]['start_time'] = $start;
        return $start;
    }

    public function endTimer(string $output_id = 'default')
    {
        if (!isset($this->output_sets[$output_id]['start_time']))
        {
            return 0;
        }

        $this->output_sets[$output_id]['end_time'] = microtime(true);
        return $this->output_sets[$output_id]['end_time'] - $this->output_sets[$output_id]['start_time'];
    }
}
