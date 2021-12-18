<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
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

        if (empty(self::$extensions_map)) {
            $this->loadDataFromDatabase();
        }
    }

    private function loadDataFromDatabase(bool $ignore_cache = false): void
    {
        $formats = array();
        $categories = array();
        $extensions_map = array();
        // TODO: Add multiple item cache read/write
        // $filetype_data = $this->cache_handler->loadArrayFromFile('filetype_data', 'filetype_data.php');
        $filetype_data = array();

        if (empty($filetype_data)) {
            $filetype_data = $this->database->executeFetchAll('SELECT * FROM "' . NEL_FILETYPES_TABLE . '"',
                PDO::FETCH_ASSOC);
            $category_data = $this->database->executeFetchAll('SELECT * FROM "' . NEL_FILETYPE_CATEGORIES_TABLE . '"',
                PDO::FETCH_ASSOC);

            // $this->cache_handler->writeArrayToFile('filetype_data', $filetype_data, 'filetype_data.php');
        }

        foreach ($category_data as $data) {
            $categories[$data['category']] = $data;
        }

        self::$categories = $categories;

        foreach ($filetype_data as $data) {
            $formats[$data['format']] = $data;
            $extensions = json_decode($data['extensions'], true);

            if (empty($extensions)) {
                continue;
            }

            foreach ($extensions as $extension) {
                $extensions_map[$extension] = $data['format'];
            }
        }

        self::$categories = $categories;
        self::$formats = $formats;
        self::$extensions_map = $extensions_map;
    }

    public function formatData(string $format = null): array
    {
        if (!is_null($format)) {
            return self::$formats[$format] ?? array();
        }

        return self::$formats;
    }

    public function extensionData(string $extension): array
    {
        $extension_data = array();

        if (!$this->isValidExtension($extension)) {
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

    private function loadSettingsIfNot(Domain $domain): void
    {
        if (!isset(self::$settings[$domain->id()])) {
            $enabled_filetypes = $domain->setting('enabled_filetypes') ?? '';
            self::$settings[$domain->id()] = json_decode($enabled_filetypes, true);
        }
    }

    public function categoryIsEnabled(Domain $domain, string $type): bool
    {
        $this->loadSettingsIfNot($domain);
        return in_array($type, $this->enabledCategories($domain));
    }

    public function formatIsEnabled(Domain $domain, string $type, string $format): bool
    {
        $this->loadSettingsIfNot($domain);
        return in_array($format, $this->enabledFormats($domain, $type));
    }

    public function extensionIsEnabled(Domain $domain, string $extension): bool
    {
        $this->loadSettingsIfNot($domain);
        $extension_data = $this->extensionData($extension);

        if (empty($extension_data)) {
            return false;
        }

        $type = $extension_data['category'];
        $format = $extension_data['format'];
        return $this->categoryIsEnabled($domain, $type) && $this->formatIsEnabled($domain, $type, $format);
    }

    public function verifyFile(string $extension, string $file): bool
    {
        $extension_data = $this->extensionData($extension);

        if (empty($extension_data)) {
            return false;
        }

        // Test with PHP first as checks should generally be better (if libmagic has a matching entry)
        $mime = mime_content_type($file);

        if ($mime !== 'application/octet-stream' && $mime === $extension_data['mime']) {
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

    public function enabledCategories(Domain $domain): array
    {
        $this->loadSettingsIfNot($domain);
        $enabled = array();

        foreach (self::$settings[$domain->id()] as $category => $settings) {
            if (isset($settings['enabled']) && $settings['enabled']) {
                $enabled[] = $category;
            }
        }

        return $enabled;
    }

    public function enabledFormats(Domain $domain, string $type): array
    {
        $this->loadSettingsIfNot($domain);
        $enabled = array();

        if (!isset(self::$settings[$domain->id()][$type]) || !isset(self::$settings[$domain->id()][$type]['formats']) ||
            !self::$settings[$domain->id()][$type]['enabled']) {
            return $enabled;
        }

        foreach (self::$settings[$domain->id()][$type]['formats'] as $format) {
            if (isset(self::$formats[$format])) {
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

        if (!isset($format_data['extensions'])) {
            return $extensions;
        }

        $extensions = json_decode($format_data['extensions']);
        return (is_array($extensions)) ? $extensions : array();
    }
}