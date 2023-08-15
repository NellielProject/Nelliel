<?php
declare(strict_types = 1);

namespace Nelliel\Domains;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielCacheInterface;
use Nelliel\Database\NellielPDO;
use PDO;

class DomainSite extends Domain implements NellielCacheInterface
{

    public function __construct(NellielPDO $database)
    {
        $this->domain_id = Domain::SITE;
        $this->database = $database;
        $this->utilitySetup();
        $this->locale();
        $this->templatePath($this->front_end_data->getTemplate($this->setting('template_id'))->getPath());
    }

    protected function loadSettings(): void
    {
        $settings = $this->cache_handler->loadArrayFromFile('domain_settings', 'domain_settings.php',
            'domains/' . $this->domain_id);

        if (empty($settings)) {
            $settings = $this->loadSettingsFromDatabase();
            $this->cache_handler->writeArrayToFile('domain_settings', $settings, 'domain_settings.php',
                'domains/' . $this->domain_id);
        }

        $this->settings = $settings;
    }

    protected function loadReferences(): void
    {
        $new_reference = array();
        $new_reference['base_path'] = NEL_PUBLIC_PATH;
        $new_reference['banners_directory'] = $this->domain_id;
        $new_reference['banners_path'] = NEL_BANNERS_FILES_PATH . $new_reference['banners_directory'] . '/';
        $new_reference['banners_web_path'] = NEL_BANNERS_WEB_PATH . rawurlencode($new_reference['banners_directory']) .
            '/';
        $new_reference['log_table'] = NEL_SYSTEM_LOGS_TABLE;
        $new_reference['title'] = (!nel_true_empty($this->setting('name'))) ? $this->setting('name') : _gettext(
            'Nelliel Imageboard');
        $new_reference['home_page'] = NEL_BASE_WEB_PATH;

        if (!nel_true_empty($this->setting('home_page'))) {
            $new_reference['home_page'] = $this->setting('home_page');
        }

        $this->references = $new_reference;
    }

    protected function loadSettingsFromDatabase(): array
    {
        $settings = array();
        $config_list = $this->database->executeFetchAll(
            'SELECT * FROM "' . NEL_SETTINGS_TABLE . '" INNER JOIN "' . NEL_SITE_CONFIG_TABLE . '" ON "' .
            NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_SITE_CONFIG_TABLE .
            '"."setting_name" WHERE "setting_category" = \'site\'', PDO::FETCH_ASSOC);

        foreach ($config_list as $config) {
            $config['setting_value'] = nel_typecast($config['setting_value'], $config['data_type']);
            $settings[$config['setting_name']] = $config['setting_value'];
        }

        return $settings;
    }

    public function updateStatistics(): void
    {}

    public function regenCache()
    {
        if (NEL_USE_FILE_CACHE) {
            $this->cacheSettings();
        }
    }

    public function deleteCache()
    {
        if (NEL_USE_FILE_CACHE) {
            $this->file_handler->eraserGun(NEL_CACHE_FILES_PATH . 'domains/' . $this->domain_id);
        }
    }

    public function exists(): bool
    {
        return true;
    }
}