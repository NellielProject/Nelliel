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
        parent::__construct(Domain::SITE, $database);
        $this->templatePath($this->front_end_data->getTemplate($this->setting('template_id'))->getPath());
    }

    protected function loadSettings(): void
    {
        $settings = array();

        if (NEL_USE_FILE_CACHE) {
            $settings = $this->cache_handler->loadArrayFromFile('domain_settings', 'domain_settings.php',
                'domains/' . $this->domain_id);
        }

        if (empty($settings)) {
            $settings = $this->loadSettingsFromDatabase();
            $this->regenCache();
        }

        $this->settings = $settings;
        $this->updateLocale($this->setting('locale'));
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
        $new_reference['home_page'] = '/';
        $this->references = $new_reference;
    }

    protected function loadSettingsFromDatabase(): array
    {
        $settings = array();
        $settings_list = $this->database->executeFetchAll(
            'SELECT "setting_name", "default_value", "data_type" FROM "' . NEL_SETTINGS_TABLE .
            '" WHERE "setting_category" = \'site\'', PDO::FETCH_ASSOC);
        $config_list = $this->database->executeFetchAll(
            'SELECT "setting_name", "setting_value" FROM "' . NEL_SITE_CONFIG_TABLE . '"', PDO::FETCH_KEY_PAIR);

        foreach ($settings_list as $setting) {
            $settings[$setting['setting_name']] = nel_typecast(
                $config_list[$setting['setting_name']] ?? $setting['default_value'], $setting['data_type'], false);
        }

        return $settings;
    }

    public function uri(bool $display = false, bool $formatted = false): string
    {
        $uri = ($display) ? $this->display_uri : $this->uri;

        if ($formatted) {
            $uri = __('Site');
        }

        return $uri;
    }

    public function url(): string
    {
        return $this->setting('absolute_url_protocol') . '://' . rtrim($this->setting('site_domain'), '/') . '/';
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
}