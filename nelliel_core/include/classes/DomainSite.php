<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class DomainSite extends Domain
{
    private $file_filters;

    public function __construct(NellielPDO $database)
    {
        $this->domain_id = '_site_';
        $this->database = $database;
        $this->utilitySetup();
        $this->locale();
        $templates_file_path = ($this->front_end_data->templateIsCore($this->setting('template_id'))) ? CORE_TEMPLATES_FILE_PATH : CUSTOM_TEMPLATES_FILE_PATH;
        $this->templatePath(
                $templates_file_path . $this->front_end_data->template($this->setting('template_id'))['directory']);
    }

    protected function loadSettings()
    {
        $settings = $this->cache_handler->loadArrayFromCache('site_settings.php', 'site_settings');

        if (empty($settings))
        {
            $settings = $this->loadSettingsFromDatabase();

            if (USE_INTERNAL_CACHE)
            {
                $this->cache_handler->writeCacheFile(CACHE_FILE_PATH . $this->domain_id . '/', 'domain_settings.php',
                        '$domain_settings = ' . var_export($settings, true) . ';');
            }
        }

        $this->domain_settings = $settings;
    }

    protected function loadReferences()
    {
        $new_reference = array();
        $this->domain_references = $new_reference;
    }

    protected function loadSettingsFromDatabase()
    {
        $settings = array();
        $config_list = $this->database->executeFetchAll('SELECT * FROM "' . SITE_CONFIG_TABLE . '"', PDO::FETCH_ASSOC);

        foreach ($config_list as $config)
        {
            $config['setting'] = nel_cast_to_datatype($config['setting'], $config['data_type'], false);
            $settings[$config['config_name']] = $config['setting'];
        }

        return $settings;
    }

    public function globalVariation()
    {
        return false;
    }

    public function regenCache()
    {
        if (USE_INTERNAL_CACHE)
        {
            $settings = $this->loadSettingsFromDatabase();
            $this->cache_handler->writeCacheFile(CACHE_FILE_PATH, 'site_settings.php',
                    '$site_settings = ' . var_export($settings, true) . ';');
        }
    }

    public function deleteCache()
    {
        if (USE_INTERNAL_CACHE)
        {
            $this->file_handler->eraserGun(CACHE_FILE_PATH, 'site_settings.php');
        }
    }

    public function fileFilters()
    {
        if (empty($this->file_filters))
        {
            $loaded = false;

            if (!$loaded)
            {
                $filters = $this->database->executeFetchAll(
                        'SELECT "hash_type", "file_hash" FROM "nelliel_file_filters"', PDO::FETCH_ASSOC);
                foreach ($filters as $filter)
                {
                    $this->file_filters[$filter['hash_type']][] = $filter['file_hash'];
                }
            }
        }

        return $this->file_filters;
    }
}