<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use Nelliel\Domains\Domain;
use PDO;

class FileTypes
{
    private $cache_handler;
    private $database;
    private static $settings;
    private static $categories_list;
    private static $formats_list;
    private static $extensions_list;
    private static $categories_map;
    private static $formats_map;
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
        $filetype_data = array();

        if (empty($filetype_data)) {
            $filetype_data = $this->database->executeFetchAll('SELECT * FROM "' . NEL_FILETYPES_TABLE . '"',
                PDO::FETCH_ASSOC);
            $category_data = $this->database->executeFetchAll('SELECT * FROM "' . NEL_FILETYPE_CATEGORIES_TABLE . '"',
                PDO::FETCH_ASSOC);
        }

        foreach ($category_data as $data) {
            $categories[$data['category']] = $data;
            self::$categories_list[] = $data['category'];
        }

        foreach ($filetype_data as $data) {
            $formats[$data['format']] = $data;
            self::$formats_list[] = $data['format'];
            $extensions = json_decode($data['extensions'], true);

            if (empty($extensions)) {
                continue;
            }

            foreach ($extensions as $extension) {
                $extensions_map[utf8_strtolower($extension)][] = $data['format'];
                self::$extensions_list[] = utf8_strtolower($extension);
            }
        }

        self::$categories_map = $categories;
        self::$formats_map = $formats;
        self::$extensions_map = $extensions_map;
    }

    public function categoryData(string $category): array
    {
        return self::$categories_map[$category] ?? array();
    }

    public function formatData(string $format): array
    {
        return self::$formats_map[$format] ?? array();
    }

    public function categories(): array
    {
        return self::$categories_list;
    }

    public function formats(): array
    {
        return self::$formats_list;
    }

    public function extensions(): array
    {
        return self::$extensions_list;
    }

    public function formatHasExtension(string $extension, string $format): bool
    {
        return in_array(utf8_strtolower($extension), $this->formatExtensions($format));
    }

    private function loadSettingsIfNot(Domain $domain): void
    {
        if (!isset(self::$settings[$domain->id()])) {
            $enabled_filetypes = json_decode($domain->setting('enabled_filetypes') ?? '', true);

            foreach ($enabled_filetypes as $category => $data) {
                if ($data['enabled'] ?? false === false) {
                    continue;
                }

                $formats = $data['formats'] ?? array();
                self::$settings[$domain->id()]['enabled_categories'][$category] = ['formats' => $formats ?? array()];

                foreach ($formats as $format) {
                    self::$settings[$domain->id()]['enabled_formats'][] = $format;
                }
            }
        }
    }

    public function categoryIsEnabled(Domain $domain, string $category): bool
    {
        return in_array($category, $this->enabledCategories($domain));
    }

    public function formatIsEnabled(Domain $domain, string $format): bool
    {
        return in_array($format, $this->enabledFormats($domain));
    }

    public function getFileFormat(string $extension, string $file): string
    {
        $extension_formats = self::$extensions_map[utf8_strtolower($extension)];
        $start_buffer = 65535;
        $end_buffer = 65535;
        $file_length = filesize($file);
        $end_offset = ($file_length < $end_buffer) ? $file_length : $file_length - $end_buffer;
        $file_test_begin = file_get_contents($file, false, null, 0, $start_buffer);
        $file_test_end = file_get_contents($file, false, null, $end_offset);

        foreach ($extension_formats as $format) {
            $format_data = $this->formatData($format);

            if (preg_match('/' . $format_data['magic_regex'] . '/s', $file_test_begin) ||
                preg_match('/' . $format_data['magic_regex'] . '/s', $file_test_end)) {
                return $format;
            }
        }

        return '';
    }

    public function getFormatMime(string $format)
    {
        $format_data = $this->formatData($format);
        $mimes = json_decode($format_data['mimetypes'], true);
        return $mimes[0];
    }

    public function enabledCategories(Domain $domain): array
    {
        $this->loadSettingsIfNot($domain);
        $enabled = array();

        foreach (self::$settings[$domain->id()]['enabled_categories'] as $category => $data) {
            $enabled[] = $category;
        }

        return $enabled;
    }

    public function enabledFormats(Domain $domain, ?string $category = null): array
    {
        $this->loadSettingsIfNot($domain);

        if (is_null($category)) {
            return self::$settings[$domain->id()]['enabled_formats'];
        }

        return self::$settings[$domain->id()]['enabled_categories'][$category]['formats'];
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