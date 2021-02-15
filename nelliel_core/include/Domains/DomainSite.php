<?php

declare(strict_types=1);

namespace Nelliel\Domains;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\NellielCacheInterface;
use Nelliel\NellielPDO;
use PDO;

class DomainSite extends Domain implements NellielCacheInterface
{

    public function __construct(NellielPDO $database)
    {
        $this->id = Domain::SITE;
        $this->database = $database;
        $this->utilitySetup();
        $this->locale();
        $this->templatePath(
                NEL_TEMPLATES_FILES_PATH . $this->front_end_data->template($this->setting('template_id'))['directory']);
    }

    protected function loadSettings(): void
    {
        $settings = $this->cache_handler->loadArrayFromFile('domain_settings', 'domain_settings.php',
                'domains/' . $this->id);

        if (empty($settings))
        {
            $settings = $this->loadSettingsFromDatabase();
            $this->cache_handler->writeArrayToFile('domain_settings', $settings, 'domain_settings.php',
                    'domains/' . $this->id);
        }

        $this->settings = $settings;
    }

    protected function loadReferences(): void
    {
        $new_reference = array();
        $new_reference['log_table'] = NEL_LOGS_TABLE;
        $new_reference['title'] = (!nel_true_empty($this->setting('name'))) ? $this->setting('name') : _gettext(
                'Nelliel Imageboard');
        $this->references = $new_reference;
    }

    protected function loadSettingsFromDatabase(): array
    {
        $settings = array();
        $config_list = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_SETTINGS_TABLE . '" INNER JOIN "' . NEL_SITE_CONFIG_TABLE . '" ON "' .
                NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_SITE_CONFIG_TABLE .
                '"."setting_name" WHERE "setting_category" = \'core\'', PDO::FETCH_ASSOC);

        foreach ($config_list as $config)
        {
            $config['setting_value'] = nel_cast_to_datatype($config['setting_value'], $config['data_type'], false);
            $settings[$config['setting_name']] = $config['setting_value'];
        }

        return $settings;
    }

    public function globalVariation()
    {
        return false;
    }

    public function regenCache()
    {
        if (NEL_USE_FILE_CACHE)
        {
            $this->cacheSettings();
        }
    }

    public function deleteCache()
    {
        if (NEL_USE_FILE_CACHE)
        {
            $this->file_handler->eraserGun(NEL_CACHE_FILES_PATH . $this->id);
        }
    }
}