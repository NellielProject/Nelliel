<?php
declare(strict_types = 1);

namespace Nelliel\FrontEnd;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielPDO;
use Nelliel\Utility\CacheHandler;
use PDO;
use Nelliel\INIParser;

class FrontEndData
{
    private $database;
    private $ini_parser;
    private $cache_handler;
    private static $image_sets = array();
    private static $styles = array();
    private static $templates = array();
    private $core_image_set_ids = array();
    private $core_style_ids = array();
    private $core_template_ids = array();

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
        $this->ini_parser = new INIParser(nel_utilities()->fileHandler());
        $this->cache_handler = new CacheHandler();
        $this->core_image_set_ids = ['images-nelliel-basic'];
        $this->core_style_ids = ['style-nelliel', 'style-nelliel-2', 'style-nelliel-classic', 'style-futaba',
            'style-burichan', 'style-nigra'];
        $this->core_template_ids = ['template-nelliel-basic'];
    }

    public function getImageSetInis(string $directory = null): array
    {
        return $this->ini_parser->parseDirectories(NEL_IMAGE_SETS_FILES_PATH, 'image_info.ini');
    }

    public function getImageSet(string $set_id): ImageSet
    {
        if (!isset(self::$image_sets[$set_id]))
        {
            self::$image_sets[$set_id] = new ImageSet($this->database, $this, $set_id);
        }

        return self::$image_sets[$set_id];
    }

    public function getAllImageSets(): array
    {
        $set_ids = $this->database->executeFetchAll(
                'SELECT "set_id" FROM "' . NEL_IMAGE_SETS_TABLE . '" ORDER BY "entry" ASC', PDO::FETCH_COLUMN);
        $sets = array();

        foreach ($set_ids as $set_id)
        {
            $sets[$set_id] = $this->getImageSet($set_id);
        }

        return $sets;
    }

    public function getBaseImageSet(): ImageSet
    {
        return $this->getImageSet(nel_site_domain()->setting('base_image_set'));
    }

    public function imageSetIsCore(string $id): bool
    {
        return in_array($id, $this->core_image_set_ids);
    }

    public function getStyleInis(): array
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
        $style_ids = $this->database->executeFetchAll(
                'SELECT "style_id" FROM "' . NEL_STYLES_TABLE . '" ORDER BY "entry" ASC', PDO::FETCH_COLUMN);
        $styles = array();

        foreach ($style_ids as $style_id)
        {
            $styles[$style_id] = $this->getStyle($style_id);
        }

        return $styles;
    }

    public function styleIsCore(string $id): bool
    {
        return in_array($id, $this->core_style_ids);
    }

    public function templateIsCore(string $id): bool
    {
        return in_array($id, $this->core_template_ids);
    }

    public function getTemplateInis(): array
    {
        return $this->ini_parser->parseDirectories(NEL_TEMPLATES_FILES_PATH, 'template_info.ini');
    }

    public function getTemplate(string $template_id): Template
    {
        if (!isset(self::$templates[$template_id]))
        {
            self::$templates[$template_id] = new Template($this->database, $this, $template_id);
        }

        return self::$templates[$template_id];
    }

    public function getAllTemplates(): array
    {
        $template_ids = $this->database->executeFetchAll(
                'SELECT "template_id" FROM "' . NEL_TEMPLATES_TABLE . '" ORDER BY "entry" ASC', PDO::FETCH_COLUMN);
        $templates = array();

        foreach ($template_ids as $template_id)
        {
            $templates[$template_id] = $this->getTemplate($template_id);
        }

        return $templates;
    }
}