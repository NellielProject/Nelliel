<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class FrontEndData
{
    private $database;
    private $css_styles = array();
    private $templates = array();
    private $default_template = array();
    private $filetype_icon_sets = array();

    function __construct($database)
    {
        $this->database = $database;
    }

    private function loadData()
    {
        $all_data = $this->database->executeFetchAll('SELECT * FROM "' . FRONT_END_TABLE . '"', PDO::FETCH_ASSOC);

        foreach ($all_data as $data)
        {
            if ($data['resource_type'] == 'css')
            {
                $this->css_styles[$data['id']] = $data;
            }
            else if ($data['resource_type'] == 'template')
            {
                $this->templates[$data['id']] = $data;
            }
            else if ($data['resource_type'] == 'default-template')
            {
                $this->default_template = $data;
            }
            else if ($data['resource_type'] == 'filetype-icon-set')
            {
                $this->filetype_icon_sets[$data['id']] = $data;
            }
        }
    }

    public function cssStyle($style = null)
    {
        if (empty($this->css_styles))
        {
            $this->loadData();
        }

        if (is_null($style))
        {
            return $this->css_styles;
        }

        return $this->css_styles[$style];
    }

    public function template($template = null, $return_default = true)
    {
        if (empty($this->templates))
        {
            $this->loadData();
        }

        if (is_null($template))
        {
            return $this->templates;
        }

        if(!isset($this->templates[$template]) && $return_default)
        {
            return $this->default_template;
        }

        return $this->templates[$template];
    }

    public function filetypeIconSet($set = null)
    {
        if (empty($this->filetype_icon_sets))
        {
            $this->loadData();
        }

        if (is_null($set))
        {
            return $this->filetype_icon_sets;
        }

        return $this->filetype_icon_sets[$set];
    }
}