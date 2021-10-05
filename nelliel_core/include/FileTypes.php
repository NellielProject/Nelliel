<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class FileTypes
{
    private $cache_handler;
    private $database;
    private static $formats;
    private static $settings;
    private static $categories;
    private static $extensions_map;

    function __construct(NellielPDO $database)
    {
        $this->cache_handler = nel_utilities()->cacheHandler();
        $this->database = $database;

        if (empty(self::$extensions_map))
        {
            $this->loadDataFromDatabase(false);
        }
    }

    private function loadDataFromDatabase(): void
    {
        $extensions = array();
        $types = array();
        $filetype_data = $this->cache_handler->loadArrayFromFile('filetype_data', 'filetype_data.php');

        if (empty($filetype_data))
        {
            $filetype_data = $this->database->executeFetchAll('SELECT * FROM "nelliel_filetypes" ORDER BY "entry" ASC',
                    PDO::FETCH_ASSOC);
            $this->cache_handler->writeArrayToFile('filetype_data', $filetype_data, 'filetype_data.php');
        }

        foreach ($filetype_data as $data)
        {
            if ($data['is_category'] == 1)
            {
                $types[$data['category']] = $data;
                continue;
            }
            else
            {
                $formats[$data['format']] = $data;
                $extensions = json_decode($data['extensions'], true);

                if (empty($extensions))
                {
                    continue;
                }

                foreach ($extensions as $extension)
                {
                    $extensions_map[$extension] = $data['format'];
                }
            }
        }

        self::$categories = $types;
        self::$formats = $formats;
        self::$extensions_map = $extensions_map;
    }

    private function loadSettingsFromDatabase(string $domain_id, bool $ignore_cache = false): void
    {
        $settings = array();

        if (!$ignore_cache)
        {
            $settings = $this->cache_handler->loadArrayFromFile('settings', 'filetype_settings.php', $domain_id);
        }

        if (empty($settings))
        {
            $prepared = $this->database->prepare(
                    'SELECT "setting_value" FROM "' . NEL_BOARD_CONFIGS_TABLE .
                    '" WHERE "board_id" = ? AND "setting_name" = ?');
            $filetypes_json = $this->database->executePreparedFetch($prepared, [$domain_id, 'enabled_filetypes'],
                    PDO::FETCH_COLUMN);
            $settings = json_decode($filetypes_json, true);
        }

        self::$settings[$domain_id] = $settings;
    }

    public function formatData(string $format = null): array
    {
        if (!is_null($format))
        {
            return self::$formats[$format] ?? array();
        }

        return self::$formats;
    }

    public function extensionData(string $extension): array
    {
        $extension_data = array();

        if (!$this->isValidExtension($extension))
        {
            return $extension_data;
        }

        $extension_data = self::$formats[self::$extensions_map[$extension]];
        return $extension_data;
    }

    public function categories(): array
    {
        return self::$categories;
    }

    public function isValidExtension(string $extension): bool
    {
        return isset(self::$extensions_map[$extension]);
    }

    private function loadSettingsIfNot(string $domain_id, bool $ignore_cache = false): void
    {
        if (!isset(self::$settings[$domain_id]))
        {
            $this->loadSettingsFromDatabase($domain_id, $ignore_cache);
        }
    }

    public function categoryIsEnabled(string $domain_id, string $type): bool
    {
        $this->loadSettingsIfNot($domain_id);
        return in_array($type, $this->enabledCategories($domain_id));
    }

    public function formatIsEnabled(string $domain_id, string $type, string $format): bool
    {
        $this->loadSettingsIfNot($domain_id);
        return in_array($format, $this->enabledFormats($domain_id, $type));
    }

    public function extensionIsEnabled(string $domain_id, string $extension): bool
    {
        $this->loadSettingsIfNot($domain_id);
        $extension_data = $this->extensionData($extension);

        if (empty($extension_data))
        {
            return false;
        }

        $type = $extension_data['category'];
        $format = $extension_data['format'];
        return $this->categoryIsEnabled($domain_id, $type) && $this->formatIsEnabled($domain_id, $type, $format);
    }

    public function verifyFile(string $extension, string $file): bool
    {
        $extension_data = $this->extensionData($extension);

        if (empty($extension_data))
        {
            return false;
        }

        // Test with PHP first as checks should generally be better (if libmagic has a matching entry)
        $mime = mime_content_type($file);

        if ($mime !== 'application/octet-stream' && $mime === $extension_data['mime'])
        {
            return true;
        }

        // Fallback to custom check if a match wasn't found
        $start_buffer = 65535;
        $end_buffer = 65535;
        $file_length = filesize($file);
        $end_offset = ($file_length < $end_buffer) ? $file_length : $file_length - $end_buffer;
        $file_test_begin = file_get_contents($file, false, null, 0, $start_buffer);
        $file_test_end = file_get_contents($file, false, null, $end_offset);
        return preg_match('/' . $extension_data['magic_regex'] . '/s', $file_test_begin) ||
                preg_match('/' . $extension_data['magic_regex'] . '/s', $file_test_end);
    }

    public function regenCache(string $domain_id): void
    {
        if (NEL_USE_FILE_CACHE)
        {
            $this->loadSettingsIfNot($domain_id, true);
            $this->cache_handler->writeArrayToFile('settings', self::$settings[$domain_id], 'filetype_settings.php',
                    'domains/' . $domain_id);
        }
    }

    public function deleteCache(string $domain_id): void
    {
        ;
    }

    public function enabledCategories(string $domain_id): array
    {
        $this->loadSettingsIfNot($domain_id);
        $enabled = array();

        foreach (self::$settings[$domain_id] as $category => $settings)
        {
            if (isset($settings['enabled']) && $settings['enabled'])
            {
                $enabled[] = $category;
            }
        }

        return $enabled;
    }

    public function enabledFormats(string $domain_id, string $type): array
    {
        $this->loadSettingsIfNot($domain_id);
        $enabled = array();

        if (!isset(self::$settings[$domain_id][$type]) || !isset(self::$settings[$domain_id][$type]['formats']) ||
                !self::$settings[$domain_id][$type]['enabled'])
        {
            return $enabled;
        }

        foreach (self::$settings[$domain_id][$type]['formats'] as $format)
        {
            if(isset(self::$formats[$format]))
            {
                $enabled[] = $format;
            }
        }

        return $enabled;
    }

    public function availableFormats(): array
    {
        return array_keys(self::$formats);
    }

    public function formatExtensions(string $format): array
    {
        $extensions = array();
        $format_data = $this->formatData($format);

        if(!isset($format_data['extensions']))
        {
            return $extensions;
        }

        $extensions = json_decode($format_data['extensions']);
        return (is_array($extensions)) ? $extensions : array();
    }
}