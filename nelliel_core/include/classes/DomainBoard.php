<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class DomainBoard extends Domain implements NellielCacheInterface
{

    public function __construct(string $domain_id, NellielPDO $database)
    {
        $this->domain_id = $domain_id;
        $this->database = $database;
        $this->utilitySetup();
        $this->locale();
        $this->templatePath(
                NEL_TEMPLATES_FILES_PATH .
                $this->front_end_data->template($this->setting('template_id'))['directory']);
        $this->global_variation = new DomainAllBoards($this->database);
    }

    protected function loadSettings()
    {
        $settings = $this->cache_handler->loadArrayFromCache($this->domain_id . '/domain_settings.php',
                'domain_settings');

        if (empty($settings))
        {
            $settings = $this->loadSettingsFromDatabase();

            if (NEL_USE_INTERNAL_CACHE)
            {
                $this->cache_handler->writeCacheFile(NEL_CACHE_FILES_PATH . $this->domain_id . '/',
                        'domain_settings.php', '$domain_settings = ' . var_export($settings, true) . ';');
            }
        }

        $this->domain_settings = $settings;
    }

    protected function loadReferences()
    {
        $prepared = $this->database->prepare('SELECT * FROM "nelliel_board_data" WHERE "board_id" = ?');
        $board_data = $this->database->executePreparedFetch($prepared, [$this->domain_id], PDO::FETCH_ASSOC);
        $new_reference = array();
        $board_path = NEL_BASE_PATH . $board_data['board_id'] . '/';
        $board_web_path = NEL_BASE_WEB_PATH . rawurlencode($board_data['board_id']) . '/';
        $new_reference['board_directory'] = $board_data['board_id'];
        $new_reference['db_prefix'] = $board_data['db_prefix'];
        $new_reference['locked'] = (bool) $board_data['locked'];
        $new_reference['src_dir'] = 'src';
        $new_reference['preview_dir'] = 'preview';
        $new_reference['page_dir'] = 'threads';
        $new_reference['archive_dir'] = 'archive';
        $new_reference['board_path'] = $board_path;
        $new_reference['board_web_path'] = $board_web_path;
        $new_reference['src_path'] = $board_path . $new_reference['src_dir'] . '/';
        $new_reference['src_web_path'] = $board_web_path . rawurlencode($new_reference['src_dir']) . '/';
        $new_reference['preview_path'] = $board_path . $new_reference['preview_dir'] . '/';
        $new_reference['preview_web_path'] = $board_web_path . rawurlencode($new_reference['preview_dir']) . '/';
        $new_reference['page_path'] = $board_path . $new_reference['page_dir'] . '/';
        $new_reference['page_web_path'] = $board_web_path . rawurlencode($new_reference['page_dir']) . '/';
        $new_reference['archive_path'] = $board_path . $new_reference['archive_dir'] . '/';
        $new_reference['archive_src_path'] = $board_path . $new_reference['archive_dir'] . '/' .
                $new_reference['src_dir'] . '/';
        $new_reference['archive_preview_path'] = $board_path . $new_reference['archive_dir'] . '/' .
                $new_reference['preview_dir'] . '/';
        $new_reference['archive_page_path'] = $board_path . $new_reference['archive_dir'] . '/' .
                $new_reference['page_dir'] . '/';
        $new_reference['posts_table'] = $new_reference['db_prefix'] . '_posts';
        $new_reference['threads_table'] = $new_reference['db_prefix'] . '_threads';
        $new_reference['content_table'] = $new_reference['db_prefix'] . '_content';
        $new_reference['config_table'] = $new_reference['db_prefix'] . '_config';
        $new_reference['log_table'] = $new_reference['db_prefix'] . '_logs';
        $this->domain_references = $new_reference;
    }

    protected function loadSettingsFromDatabase()
    {
        $settings = array();
        $prepared = $this->database->prepare(
                'SELECT "db_prefix" FROM "' . NEL_BOARD_DATA_TABLE . '" WHERE "board_id" = ?');
        $db_prefix = $this->database->executePreparedFetch($prepared, [$this->domain_id], PDO::FETCH_COLUMN);
        $config_table = $db_prefix . '_config';
        $config_list = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_SETTINGS_TABLE . '" INNER JOIN "' . $config_table . '" ON "' . NEL_SETTINGS_TABLE .
                '"."setting_name" = "' . $config_table . '"."setting_name" WHERE "setting_category" = \'board\'',
                PDO::FETCH_ASSOC);

        foreach ($config_list as $config)
        {
            $config['setting_value'] = nel_cast_to_datatype($config['setting_value'], $config['data_type'], false);
            $settings[$config['setting_name']] = $config['setting_value'];
        }

        return $settings;
    }

    public function globalVariation()
    {
        return new DomainAllBoards($this->database);
    }

    public function multiVariation()
    {
        return new DomainMultiBoard($this->database);
    }

    public function boardExists()
    {
        $prepared = $this->database->prepare('SELECT 1 FROM "nelliel_board_data" WHERE "board_id" = ?');
        $board_data = $this->database->executePreparedFetch($prepared, [$this->domain_id], PDO::FETCH_COLUMN);
        return !empty($board_data);
    }

    public function regenCache()
    {
        if (NEL_USE_INTERNAL_CACHE)
        {
            $this->cacheSettings();
            $filetypes = new FileTypes($this->database());
            $filetypes->generateSettingsCache($this->domain_id);
        }
    }

    public function deleteCache()
    {
        if (NEL_USE_INTERNAL_CACHE)
        {
            $this->file_handler->eraserGun(NEL_CACHE_FILES_PATH . $this->domain_id);
        }
    }
}