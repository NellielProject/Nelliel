<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class FrontEndData
{
    private $database;
    private $ini_parser;
    private $styles = array();
    private $default_style = array();
    private $templates = array();
    private $default_template = array();
    private $icon_sets = array();
    private $default_icon_set = array();
    private $core_icon_set_ids = array();
    private $core_style_ids = array();
    private $core_template_ids = array();

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
        $this->ini_parser = new INIParser(nel_utilities()->fileHandler());
        $this->core_icon_set_ids = ['icons-nelliel-basic'];
        $this->core_style_ids = ['style-nelliel', 'style-nelliel-b', 'style-futaba', 'style-burichan', 'style-nigra'];
        $this->core_template_ids = ['template-nelliel-basic'];
    }

    private function loadStylesData()
    {
        $all_data = $this->database->executeFetchAll('SELECT * FROM "' . NEL_ASSETS_TABLE . '" WHERE "type" = \'style\'',
                PDO::FETCH_ASSOC);

        foreach ($all_data as $data)
        {
            $info = json_decode($data['info'], true);

            if ($data['is_default'] == 1)
            {
                $this->default_style = $info;
            }
            else
            {
                $this->styles[$data['id']] = $info;
            }
        }
    }

    private function loadIconSetData()
    {
        $all_data = $this->database->executeFetchAll('SELECT * FROM "' . NEL_ASSETS_TABLE . '" WHERE "type" = \'icon-set\'',
                PDO::FETCH_ASSOC);

        foreach ($all_data as $data)
        {
            $info = json_decode($data['info'], true);

            if ($data['is_default'] == 1)
            {
                $this->default_icon_set = $info;
            }
            else
            {
                $this->icon_sets[$data['id']] = $info;
            }
        }
    }

    private function loadTemplateData()
    {
        $all_data = $this->database->executeFetchAll('SELECT * FROM "' . NEL_TEMPLATES_TABLE . '"', PDO::FETCH_ASSOC);

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

    public function styleIsCore(string $id)
    {
        return in_array($id, $this->core_style_ids);
    }

    public function getStyleInis()
    {
        return $this->ini_parser->parseDirectories(NEL_STYLES_FILES_PATH, 'style_info.ini');
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

    public function templateIsCore(string $id)
    {
        return in_array($id, $this->core_template_ids);
    }

    public function getTemplateInis()
    {
        return $this->ini_parser->parseDirectories(NEL_TEMPLATES_FILES_PATH, 'template_info.ini');
    }

    public function iconSet($set = null, bool $return_default = true)
    {
        if (empty($this->icon_sets))
        {
            $this->loadIconSetData();
        }

        if (is_null($set))
        {
            return $this->icon_sets;
        }

        if (!isset($this->icon_sets[$set]) && $return_default)
        {
            return $this->default_icon_set;
        }

        return $this->icon_sets[$set];
    }

    public function iconSetIsCore(string $id)
    {
        return in_array($id, $this->core_icon_set_ids);
    }

    public function getIconSetInis()
    {
        return $this->ini_parser->parseDirectories(NEL_ICON_SETS_FILES_PATH, 'icons_info.ini');
    }
}