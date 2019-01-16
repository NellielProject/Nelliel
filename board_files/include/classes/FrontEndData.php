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
    private $styles = array();
    private $default_style = array();
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
        $all_data = $this->database->executeFetchAll('SELECT * FROM "' . ASSETS_TABLE . '" WHERE "type" = \'style\'', PDO::FETCH_ASSOC);

        foreach ($all_data as $data)
        {
            $info = json_decode($data['info'], true);

            if ($data['is_default'] == 1)
            {
                $this->default_style = $info;
            }
            else
            {
                $this->cstyles[$data['id']] = $info;
            }
        }
    }

    private function loadFiletypeIconData()
    {
        $all_data = $this->database->executeFetchAll('SELECT * FROM "' . ASSETS_TABLE . '" WHERE "type" = \'icon-set\'',
                PDO::FETCH_ASSOC);

        foreach ($all_data as $data)
        {
            $info = json_decode($data['info'], true);

            if ($info['set_type'] !== 'filetype')
            {
                continue;
            }

            if ($data['is_default'] == 1)
            {
                $this->default_filetype_icon_set = $info;
            }
            else
            {
                $this->templates[$data['id']] = $info;
            }
        }
    }

    private function loadTemplateData()
    {
        $all_data = $this->database->executeFetchAll('SELECT * FROM "' . TEMPLATES_TABLE . '"',
                PDO::FETCH_ASSOC);

        foreach ($all_data as $data)
        {
            $info = json_decode($data['info'], true);

            if ($data['is_default'] == 1)
            {
                $this->default_template = $info;
            }
            else
            {
                $this->templates[$data['id']] = $info;
            }
        }
    }

    public function style($style = null, bool $return_default = true)
    {
        if (empty($this->styles))
        {
            $this->loadStylesData();
        }

        if (is_null($style))
        {
            return $this->styles;
        }

        if (!isset($this->styles[$template]) && $return_default)
        {
            return $this->default_css_style;
        }

        return $this->styles[$style];
    }

    public function template($template = null, bool $return_default = true)
    {
        if (empty($this->templates))
        {
            $this->loadTemplateData();
        }

        if (is_null($template))
        {
            return $this->templates;
        }

        if (!isset($this->templates[$template]) && $return_default)
        {
            return $this->default_template;
        }

        return $this->templates[$template];
    }

    public function filetypeIconSet($set = null, bool $return_default = true)
    {
        if (empty($this->filetype_icon_sets))
        {
            $this->loadFiletypeIconData();
        }

        if (is_null($set))
        {
            return $this->filetype_icon_sets;
        }

        if (!isset($this->filetype_icon_sets[$set]) && $return_default)
        {
            return $this->default_filetype_icon_set;
        }

        return $this->filetype_icon_sets[$set];
    }
}