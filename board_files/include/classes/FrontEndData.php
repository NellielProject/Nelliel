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
    private $default_css_style = array();
    private $templates = array();
    private $default_template = array();
    private $filetype_icon_sets = array();
    private $default_filetype_icon_set = array();

    function __construct($database)
    {
        $this->database = $database;
    }

    private function loadStylesData()
    {
        $all_data = $this->database->executeFetchAll('SELECT * FROM "' . STYLES_TABLE . '"', PDO::FETCH_ASSOC);

        foreach ($all_data as $data)
        {
            if ($data['is_default'] == 1)
            {
                $this->default_css_style = $data;
            }
            else
            {
                $this->css_styles[$data['id']] = $data;
            }
        }
    }

    private function loadFiletypeIconData()
    {
        $all_data = $this->database->executeFetchAll('SELECT * FROM "' . ICON_SET_TABLE . '" WHERE "set_type" = \'filetype\'', PDO::FETCH_ASSOC);

        foreach ($all_data as $data)
        {
            if ($data['is_default'] == 1)
            {
                $this->default_filetype_icon_set = $data;
            }
            else
            {
                $this->filetype_icon_sets[$data['id']] = $data;
            }
        }
    }

    private function loadTemplateData()
    {
        $all_data = $this->database->executeFetchAll('SELECT * FROM "' . TEMPLATE_TABLE . '"', PDO::FETCH_ASSOC);

        foreach ($all_data as $data)
        {
            if ($data['is_default'] == 1)
            {
                $this->default_template = $data;
            }
            else
            {
                $this->templates[$data['id']] = $data;
            }
        }
    }

    public function style($style = null, $return_default = true)
    {
        if (empty($this->css_styles))
        {
            $this->loadStylesData();
        }

        if (is_null($style))
        {
            return $this->css_styles;
        }

        if(!isset($this->css_styles[$template]) && $return_default)
        {
            return $this->default_css_style;
        }

        return $this->css_styles[$style];
    }

    public function template($template = null, $return_default = true)
    {
        if (empty($this->templates))
        {
            $this->loadTemplateData();
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

    public function filetypeIconSet($set = null, $return_default = true)
    {
        if (empty($this->filetype_icon_sets))
        {
            $this->loadFiletypeIconData();
        }

        if (is_null($set))
        {
            return $this->filetype_icon_sets;
        }

        if(!isset($this->filetype_icon_sets[$set]) && $return_default)
        {
            return $this->default_filetype_icon_set;
        }

        return $this->filetype_icon_sets[$set];
    }
}