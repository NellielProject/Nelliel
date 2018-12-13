<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Domain
{
    private $domain_id;
    private $domain_settings;
    private $domain_references;
    private $cache_handler;
    private $database;
    private $render_instance;

    public function __construct($domain_id, $cache_handler, $database)
    {
        $this->domain_id = $domain_id;
        $this->cache_handler = $cache_handler;
        $this->database = $database;
    }

    public function id()
    {
        return $this->domain_id;
    }

    public function setting($setting = null)
    {
        if (empty($this->domain_settings))
        {
            if ($this->domain_id === '')
            {
                $this->loadSiteSettings();
            }
            else
            {
                $this->loadBoardSettings();
            }
        }

        if (is_null($setting))
        {
            return $this->domain_settings;
        }

        return $this->domain_settings[$setting];
    }

    public function reference($reference = null)
    {
        if (empty($this->domain_references))
        {
            $prepared = $this->database->prepare('SELECT * FROM "nelliel_board_data" WHERE "board_id" = ?');
            $board_data = $this->database->executePreparedFetch($prepared, array($this->domain_id), PDO::FETCH_ASSOC);
            $new_reference = array();
            $board_path = BASE_PATH . $board_data['board_directory'] . '/';
            $new_reference['board_directory'] = $board_data['board_directory'];
            $new_reference['db_prefix'] = $board_data['db_prefix'];
            $new_reference['locked'] = (bool) $board_data['locked'];
            $new_reference['src_dir'] = 'src';
            $new_reference['thumb_dir'] = 'thumb';
            $new_reference['page_dir'] = 'threads';
            $new_reference['archive_dir'] = 'archive';
            $new_reference['board_path'] = $board_path;
            $new_reference['src_path'] = $board_path . $new_reference['src_dir'] . '/';
            $new_reference['thumb_path'] = $board_path . $new_reference['thumb_dir'] . '/';
            $new_reference['page_path'] = $board_path . $new_reference['page_dir'] . '/';
            $new_reference['archive_path'] = $board_path . $new_reference['archive_dir'] . '/';
            $new_reference['archive_src_path'] = $board_path . $new_reference['archive_dir'] . '/' .
                    $new_reference['src_dir'] . '/';
            $new_reference['archive_thumb_path'] = $board_path . $new_reference['archive_dir'] . '/' .
                    $new_reference['thumb_dir'] . '/';
            $new_reference['archive_page_path'] = $board_path . $new_reference['archive_dir'] . '/' .
                    $new_reference['page_dir'] . '/';
            $new_reference['post_table'] = $new_reference['db_prefix'] . '_posts';
            $new_reference['thread_table'] = $new_reference['db_prefix'] . '_threads';
            $new_reference['content_table'] = $new_reference['db_prefix'] . '_content';
            $new_reference['archive_post_table'] = $new_reference['db_prefix'] . '_archive_posts';
            $new_reference['archive_thread_table'] = $new_reference['db_prefix'] . '_archive_threads';
            $new_reference['archive_content_table'] = $new_reference['db_prefix'] . '_archive_content';
            $new_reference['config_table'] = $new_reference['db_prefix'] . '_config';
            $this->domain_references = $new_reference;
        }

        if (is_null($reference))
        {
            return $this->domain_references;
        }

        return $this->domain_references[$reference];
    }

    private function loadBoardSettings()
    {
        $settings = $this->cache_handler->loadArrayFromCache($this->domain_id . '/domain_settings.php',
                'domain_settings');

        if (empty($settings))
        {
            $settings = $this->loadBoardSettingsFromDatabase();

            if (USE_INTERNAL_CACHE)
            {
                $this->cache_handler->writeCacheFile(CACHE_PATH . $this->domain_id . '/', 'domain_settings.php',
                        '$domain_settings = ' . var_export($settings, true) . ';');
            }
        }

        $this->domain_settings = $settings;
    }

    private function loadSiteSettings()
    {
        $settings = $this->cache_handler->loadArrayFromCache('site_settings.php', 'site_settings');

        if (empty($settings))
        {
            $settings = $this->loadSiteSettingsFromDatabase();

            if (USE_INTERNAL_CACHE)
            {
                $this->cache_handler->writeCacheFile(CACHE_PATH . $this->domain_id . '/', 'domain_settings.php',
                        '$domain_settings = ' . var_export($settings, true) . ';');
            }
        }

        $this->domain_settings = $settings;
    }

    private function loadBoardSettingsFromDatabase()
    {
        $settings = array();
        $prepared = $this->database->prepare('SELECT "db_prefix" FROM "' . BOARD_DATA_TABLE . '" WHERE "board_id" = ?');
        $db_prefix = $this->database->executePreparedFetch($prepared, array($this->domain_id), PDO::FETCH_COLUMN);
        $config_table = $db_prefix . '_config';
        $config_list = $this->database->executeFetchAll(
                'SELECT * FROM "' . $config_table . '" WHERE "config_type" = \'board_setting\'', PDO::FETCH_ASSOC);

        foreach ($config_list as $config)
        {
            $config['setting'] = nel_cast_to_datatype($config['setting'], $config['data_type']);
            $settings[$config['config_name']] = $config['setting'];
        }

        return $settings;
    }

    private function loadSiteSettingsFromDatabase()
    {
        $settings = array();
        $config_list = $this->database->executeFetchAll('SELECT * FROM "' . SITE_CONFIG_TABLE . '"', PDO::FETCH_ASSOC);

        foreach ($config_list as $config)
        {
            $config['setting'] = nel_cast_to_datatype($config['setting'], $config['data_type']);
            $settings[$config['config_name']] = $config['setting'];
        }

        return $settings;
    }

    public function regenCache()
    {
        if (USE_INTERNAL_CACHE)
        {
            if ($this->domain_id === '')
            {
                $settings = $this->loadSiteSettingsFromDatabase();
                $this->cache_handler->writeCacheFile(CACHE_PATH, 'site_settings.php',
                        '$site_settings = ' . var_export($settings, true) . ';');
            }
            else
            {
                $settings = $this->loadBoardSettingsFromDatabase();
                $this->cache_handler->writeCacheFile(CACHE_PATH . $this->domain_id . '/', 'domain_settings.php',
                        '$domain_settings = ' . var_export($settings, true) . ';');
            }
        }
    }

    public function renderInstance($new_instance = null)
    {
        if (!is_null($new_instance))
        {
            $this->render_instance = $new_instance;
            $front_end_data = new \Nelliel\FrontEndData($this->database);
            $template_path = TEMPLATE_PATH . $front_end_data->template($this->setting('template_id'))['location'];
            $this->render_instance->getTemplateInstance()->setTemplatePath($template_path); // TODO: Update for new front end stuff
        }

        return $this->render_instance;
    }
}