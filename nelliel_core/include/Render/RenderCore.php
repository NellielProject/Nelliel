<?php

declare(strict_types=1);

namespace Nelliel\Render;

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

    public function outputExists(string $output_id)
    {
        return isset($this->output_sets[$output_id]);
    }

    public function createOutput(string $output_id = 'default')
    {
        if (!$this->outputExists($output_id))
        {
            $this->output_sets[$output_id] = array();
        }

        if (!isset($this->output_sets[$output_id]['content']))
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
        if ($this->outputExists($output_id))
        {
            return $this->output_sets[$output_id]['content'];
        }

        return '';
    }
}
