<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Utility\CacheHandler;
use PDO;

class FrontEndData
{
    private $database;
    private $ini_parser;
    private $cache_handler;
    private static $styles = array();
    private static $default_style = array();
    private static $templates = array();
    private static $default_template = array();
    private static $icon_sets = array();
    private static $default_icon_set = array();
    private $core_icon_set_ids = array();
    private $core_style_ids = array();
    private $core_template_ids = array();

    function __construct(NellielPDO $database, bool $clear = false)
    {
        $this->database = $database;
        $this->ini_parser = new INIParser(nel_utilities()->fileHandler());
        $this->cache_handler = new CacheHandler();
        $this->core_icon_set_ids = ['icons-nelliel-basic'];
        $this->core_style_ids = ['style-nelliel', 'style-nelliel-2', 'style-nelliel-classic', 'style-futaba', 'style-burichan', 'style-nigra'];
        $this->core_template_ids = ['template-nelliel-basic'];

        if ($clear)
        {
            $this->clearStatic();
        }
    }

    private function clearStatic()
    {
        self::$default_icon_set = array();
        self::$icon_sets = array();
        self::$default_style = array();
        self::$styles = array();
        self::$default_template = array();
        self::$templates = array();
    }

    private function loadStylesData()
    {
        $styles_data = $this->cache_handler->loadArrayFromFile('styles_data', 'styles_data.php');

        if (empty($styles_data))
        {
            $styles_data = $this->database->executeFetchAll(
                    'SELECT * FROM "' . NEL_ASSETS_TABLE . '" WHERE "type" = \'style\'', PDO::FETCH_ASSOC);
            $this->cache_handler->writeArrayToFile('styles_data', $styles_data, 'styles_data.php');
        }

        foreach ($styles_data as $data)
        {
            $info = json_decode($data['info'], true);

            if ($data['is_default'] == 1)
            {
                self::$default_style = $info;
            }

            self::$styles[$info['id']] = $info;
        }
    }

    private function loadIconSetData()
    {
        $icon_set_data = $this->cache_handler->loadArrayFromFile('icon_set_data', 'icon_set_data.php');

        if (empty($icon_set_data))
        {
            $icon_set_data = $this->database->executeFetchAll(
                    'SELECT * FROM "' . NEL_ASSETS_TABLE . '" WHERE "type" = \'icon-set\'', PDO::FETCH_ASSOC);
            $this->cache_handler->writeArrayToFile('icon_set_data', $icon_set_data, 'icon_set_data.php');
        }

        foreach ($icon_set_data as $data)
        {
            $info = json_decode($data['info'], true);

            if ($data['is_default'] == 1)
            {
                self::$default_icon_set = $info;
            }

            self::$icon_sets[$info['id']] = $info;
        }
    }

    private function loadTemplateData()
    {
        $template_data = $this->cache_handler->loadArrayFromFile('template_data', 'template_data.php');

        if (empty($template_data))
        {
            $template_data = $this->database->executeFetchAll('SELECT * FROM "' . NEL_TEMPLATES_TABLE . '"',
                    PDO::FETCH_ASSOC);
            $this->cache_handler->writeArrayToFile('template_data', $template_data, 'template_data.php');
        }

        foreach ($template_data as $data)
        {
            $info = json_decode($data['info'], true);

            if ($data['is_default'] == 1)
            {
                self::$default_template = $info;
            }

            self::$templates[$info['id']] = $info;
        }
    }

    public function style($style = null, bool $return_default = true)
    {
        if (empty(self::$styles))
        {
            $this->loadStylesData();
        }

        if (is_null($style))
        {
            return self::$styles;
        }

        if (!isset(self::$styles[$style]) && $return_default)
        {
            return $this->default_css_style;
        }

        return self::$styles[$style];
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
        if (empty(self::$templates))
        {
            $this->loadTemplateData();
        }

        if (is_null($template))
        {
            return self::$templates;
        }

        if (!isset(self::$templates[$template]) && $return_default)
        {
            return self::$default_template;
        }

        return self::$templates[$template];
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
        if (empty(self::$icon_sets))
        {
            $this->loadIconSetData();
        }

        if (is_null($set))
        {
            return self::$icon_sets;
        }

        if (!isset(self::$icon_sets[$set]) && $return_default)
        {
            return self::$default_icon_set;
        }

        return self::$icon_sets[$set];
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