<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Utility\CacheHandler;
use PDO;
use Nelliel\Assets\IconSet;
use Nelliel\Assets\Style;

class FrontEndData
{
    private $database;
    private $ini_parser;
    private $cache_handler;
    private static $styles = array();
    private static $templates = array();
    private static $default_template = array();
    private $core_icon_set_ids = array();
    private $core_style_ids = array();
    private $core_template_ids = array();
    private $base_icon_set_id = 'icons-nelliel-basic';
    private static $icon_sets = array();

    function __construct(NellielPDO $database, bool $clear = false)
    {
        $this->database = $database;
        $this->ini_parser = new INIParser(nel_utilities()->fileHandler());
        $this->cache_handler = new CacheHandler();
        $this->core_icon_set_ids = ['icons-nelliel-basic'];
        $this->core_style_ids = ['style-nelliel', 'style-nelliel-2', 'style-nelliel-classic', 'style-futaba',
            'style-burichan', 'style-nigra'];
        $this->core_template_ids = ['template-nelliel-basic'];

        if ($clear)
        {
            $this->clearStatic();
        }
    }

    private function clearStatic()
    {
        self::$default_template = array();
        self::$templates = array();
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

    public function getStyleInis()
    {
        return $this->ini_parser->parseDirectories(NEL_STYLES_FILES_PATH, 'style_info.ini');
    }

    public function getStyle(string $style_id): Style
    {
        if (!isset(self::$styles[$style_id]))
        {
            self::$styles[$style_id] = new Style($this->database, $this, $style_id);
        }

        return self::$styles[$style_id];
    }

    public function getAllStyles(): array
    {
        $set_ids = $this->database->executeFetchAll(
                'SELECT "style_id" FROM "' . NEL_STYLES_TABLE . '" ORDER BY "entry" ASC', PDO::FETCH_COLUMN);
        $sets = array();

        foreach ($set_ids as $set_id)
        {
            $sets[$set_id] = $this->getStyle($set_id);
        }

        return $sets;
    }

    public function styleIsCore(string $id)
    {
        return in_array($id, $this->core_style_ids);
    }

    public function getIconSetInis()
    {
        return $this->ini_parser->parseDirectories(NEL_ICON_SETS_FILES_PATH, 'icon_info.ini');
    }

    public function getIconSet(string $set_id): IconSet
    {
        if (!isset(self::$icon_sets[$set_id]))
        {
            self::$icon_sets[$set_id] = new IconSet($this->database, $this, $set_id);
        }

        return self::$icon_sets[$set_id];
    }

    public function getAllIconSets(): array
    {
        $set_ids = $this->database->executeFetchAll(
                'SELECT "set_id" FROM "' . NEL_ICON_SETS_TABLE . '" ORDER BY "entry" ASC', PDO::FETCH_COLUMN);
        $sets = array();

        foreach ($set_ids as $set_id)
        {
            $sets[$set_id] = $this->getIconSet($set_id);
        }

        return $sets;
    }

    public function iconSetIsCore(string $id)
    {
        return in_array($id, $this->core_icon_set_ids);
    }

    public function getDefaultIconSet(): IconSet
    {
        return $this->getIconSet($this->default_icon_set_id);
    }
}