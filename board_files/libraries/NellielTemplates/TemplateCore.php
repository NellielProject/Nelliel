<?php

namespace NellielTemplates;

class TemplateCore
{
    private $render_instance;
    private $templates = array();
    private $template_path = '';

    function __construct($render_instance)
    {
        $this->render_instance = $render_instance;
    }

    public function setTemplatePath($path)
    {
        if (substr($path, -1) !== '/')
        {
            $path .= '/';
        }

        $this->template_path = $path;
    }

    private function initTemplateData($template)
    {
        $this->templates[$template]['contents'] = '';
    }

    public function loadTemplateFromString($template, $contents)
    {
        $this->templates[$template]['contents'] = $contents;
    }

    public function loadTemplateFromFile($template_file)
    {
        if (!isset($this->templates[$template_file]))
        {
            $this->initTemplateData($template_file);
        }

        if (file_exists($this->template_path . $template_file))
        {
            $this->templates[$template_file]['contents'] = file_get_contents($this->template_path . $template_file);
        }
    }

    public function getTemplate($template, $raw = false)
    {
        if (!isset($this->templates[$template]))
        {
            $this->initTemplateData($template);
        }

        if ($this->templates[$template]['contents'] === '')
        {
            $this->loadTemplateFromFile($template);
            $this->checkHTMLFixes($template);
        }

        if ($raw)
        {
            return $this->templates[$template]['contents'];
        }
        else
        {
            return $this->fixInputHTML($template);
        }
    }

    public function outputHTMLFromDom($dom, $template)
    {
        $output = $dom->saveHTML();

        if(!is_null($template))
        {
            $output = $this->fixOutputHTML($template, $output);
            $output = $this->html5Fixes($template, $output);
        }

        return $output;
    }

    private function checkHTMLFixes($template)
    {
        $template_contents = $this->templates[$template]['contents'];
        $this->templates[$template]['fix_status']['doctype'] = (preg_match('#<!DOCTYPE#', $template_contents) === 0) ? true : false;
        $this->templates[$template]['fix_status']['html_open'] = (preg_match('#<html>#', $template_contents) === 0) ? true : false;
        $this->templates[$template]['fix_status']['html_close'] = (preg_match('#</html>#', $template_contents) === 0) ? true : false;
        $this->templates[$template]['fix_status']['body_open'] = (preg_match('#<body>#', $template_contents) === 0) ? true : false;
        $this->templates[$template]['fix_status']['body_close'] = (preg_match('#<\/body>#', $template_contents) === 0) ? true : false;
    }

    private function fixInputHTML($template)
    {
        $template_contents = $this->templates[$template]['contents'];

        if ($this->templates[$template]['fix_status']['html_open'])
        {
            $template_contents = '<html>' . $template_contents;
        }

        if ($this->templates[$template]['fix_status']['html_close'])
        {
            $template_contents = $template_contents . '</html>';
        }

        if ($this->templates[$template]['fix_status']['doctype'])
        {
            $template_contents = '<!DOCTYPE html>' . $template_contents;
        }

        return $template_contents;
    }

    private function fixOutputHTML($template, $template_contents)
    {
        if ($this->templates[$template]['fix_status']['doctype'])
        {
            $template_contents = preg_replace('#<!DOCTYPE html>#', '', $template_contents);
        }

        if ($this->templates[$template]['fix_status']['html_open'])
        {
            $template_contents = preg_replace('#<html>#', '', $template_contents);
        }

        if ($this->templates[$template]['fix_status']['body_open'])
        {
            $template_contents = preg_replace('#<body>#', '', $template_contents);
        }

        if ($this->templates[$template]['fix_status']['html_close'])
        {
            $template_contents = preg_replace('#<\/html>#', '', $template_contents);
        }

        if ($this->templates[$template]['fix_status']['body_close'])
        {
            $template_contents = preg_replace('#<\/body>#', '', $template_contents);
        }

        return $template_contents;
    }

    private function html5Fixes($template, $template_contents)
    {
        $template_contents = preg_replace('#<\/source>#', '', $template_contents);
        $template_contents = preg_replace('#<\/embed>#', '', $template_contents);

        return $template_contents;
    }
}