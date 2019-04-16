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

    public function templatePath($new_path = null)
    {
        if (!is_null($new_path))
        {
            if (substr(new_path, -1) !== '/')
            {
                $new_path .= '/';
            }
        }

        $this->template_path = $new_path;
        return $this->template_path;
    }

    private function initTemplateData($template)
    {
        $this->templates[$template]['contents'] = '';
    }

    public function loadTemplateFromString($template, $contents)
    {
        $this->templates[$template]['contents'] = $contents;
        $this->checkHTMLFixes($template);
    }

    public function loadTemplateFromFile($template)
    {
        if (!isset($this->templates[$template]))
        {
            $this->initTemplateData($template);
        }

        if (file_exists($this->template_path . $template))
        {
            $this->templates[$template]['contents'] = file_get_contents($this->template_path . $template);
        }

        $this->checkHTMLFixes($template);
    }

    public function getTemplate($template, $do_fixes)
    {
        if (!isset($this->templates[$template]))
        {
            $this->initTemplateData($template);
        }

        if ($do_fixes)
        {
            return $this->fixInputHTML($template);
        }
        else
        {
            return $this->templates[$template]['contents'];
        }
    }

    public function outputHTMLFromDom($dom, $template)
    {
        $output = $dom->saveHTML();

        if (!is_null($template))
        {
            $output = $this->fixOutputHTML($template, $output);
            $output = $this->html5Fixes($template, $output);
        }

        return $output;
    }

    private function checkHTMLFixes($template)
    {
        $template_contents = $this->templates[$template]['contents'];
        $this->templates[$template]['fix_status']['doctype'] = (preg_match('#<!DOCTYPE.*?>#', $template_contents) === 0) ? true : false;
        $this->templates[$template]['fix_status']['html_open'] = (preg_match('#<html.*?>#', $template_contents) === 0) ? true : false;
        $this->templates[$template]['fix_status']['html_close'] = (preg_match('#</html>#', $template_contents) === 0) ? true : false;
        $this->templates[$template]['fix_status']['body_open'] = (preg_match('#<body.*?>#', $template_contents) === 0) ? true : false;
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
        // The following elements were in HTML4 spec and are already handled properly:
        // area, base, br, col, hr, img, input, link, meta, param
        // Newer HTML5 void elements are fixed below
        $template_contents = preg_replace('#<\/embed>#', '', $template_contents);
        $template_contents = preg_replace('#<\/source>#', '', $template_contents);
        $template_contents = preg_replace('#<\/track>#', '', $template_contents);
        $template_contents = preg_replace('#<\/wbr>#', '', $template_contents);

        // These are deprecated orunsupported but we include just in case
        $template_contents = preg_replace('#<\/command>#', '', $template_contents);
        $template_contents = preg_replace('#<\/keygen>#', '', $template_contents);

        return $template_contents;
    }
}